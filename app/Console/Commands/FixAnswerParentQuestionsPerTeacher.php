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
use tcCore\User;

class FixAnswerParentQuestionsPerTeacher extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:teacherApq {username : email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix missing answer parent questions for a teacher';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        set_time_limit(0);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $start = microtime(true);
        $username = $this->argument('username');
        if(!$username){
            $this->error('We need to have an email address for the teacher in order to fix');
            exit;
        }

        $user = User::where('username',$username)->first();
        if($user == null){
            $this->error('Teacher not found');
            exit;
        }

        if($user->teacher()->count() == 0){
            $this->error('This doesn`t seem to be a teacher');
            exit;
        }

        $obj = (object) [
            'tests' => 0,
            'answers'=> 0
            ];
        $user->testtakes->each(function(TestTake $testTake) use ($obj) {
            $questions = QuestionGatherer::getQuestionsOfTest($testTake->test->getkey(), true);
            $obj->tests++;
            $questionParentAr = [];
            foreach($questions as $dottedId => $question){
                $idAr = explode('.',$dottedId);
                if(count($idAr) == 2) { // group question
                    $questionParentAr[(int) $idAr[1]] = (int) $idAr[0];
                }
            }

            $testTake->testParticipants->each(function(TestParticipant $tp) use ($questionParentAr, $obj){
                $tp->answers->each(function(Answer $a) use ($questionParentAr, $obj){
                    if(array_key_exists($a->question_id, $questionParentAr)) {
                        $apq = AnswerParentQuestion::firstOrCreate([
                            'answer_id' => $a->getKey(),
                            'group_question_id' => $questionParentAr[$a->question_id]
                        ]);
                        if($apq->wasRecentlyCreated){
                            $obj->answers++;
                        }
                    }
                });
            });
        });

        $duration = microtime(true) - $start;
        $this->info(sprintf('done, checked %d test takes and added %d AnswerParentQuestion records in %f seconds ',$obj->tests, $obj->answers, $duration));
    }
}
