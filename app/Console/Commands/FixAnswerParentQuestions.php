<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Log;
use tcCore\TestParticipant;
use tcCore\TestTake;

class FixAnswerParentQuestions extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:apq {testTakeId : TestTakeId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix missing answer parent questions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $start = microtime(true);
        $testTakeId = (int) $this->argument('testTakeId');
        if(!$testTakeId || $testTakeId < 1){
            $this->error('We need to have a testTakeId in order to fix');
            exit;
        }
        $testTake = TestTake::findOrFail($testTakeId);
        $questions = QuestionGatherer::getQuestionsOfTest($testTake->test->getkey(), true);
        $questionParentAr = [];
        foreach($questions as $dottedId => $question){
            $idAr = explode('.',$dottedId);
            if(count($idAr) == 2) { // group question
                $questionParentAr[(int) $idAr[1]] = (int) $idAr[0];
            }
        }

       $count = 0;
        $testTake->testParticipants->each(function(TestParticipant $tp) use ($questionParentAr, &$count){
           $tp->answers->each(function(Answer $a) use ($questionParentAr, &$count){
               if(array_key_exists($a->question_id, $questionParentAr)) {
                   $apq = AnswerParentQuestion::firstOrCreate([
                       'answer_id' => $a->getKey(),
                       'group_question_id' => $questionParentAr[$a->question_id]
                   ]);
                    if($apq->wasRecentlyCreated){
                        $count++;
                    }
               }
           });
        });

        $duration = microtime(true) - $start;
        $this->info(sprintf('done, added %d AnswerParentQuestion records in %f seconds',$count, $duration));
    }
}
