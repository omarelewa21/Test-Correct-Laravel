<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\Http\Helpers\AnswerParentQuestionsHelper;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Log;
use tcCore\SchoolLocation;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;

class FixAnswerParentQuestionsPerSchoolLocation extends Command
{

    use BaseCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:schoolApq {id : school location id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix missing answer parent questions for all teachers of a school location';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        set_time_limit(300);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $start = microtime(true);
        $id = (int) $this->argument('id');
        if(!$id || $id < 1){
            $this->error('We need to have an id of the school location in order to fix');
            exit;
        }

        $schoolLocation = SchoolLocation::findOrFail($id);

        $obj = (new AnswerParentQuestionsHelper($this))->fixAnswerParentQuestionsPerSchoolLocation($schoolLocation);
//        $users = User::whereNotNull('abbreviation')->where('school_location_id',$id)->get();
//        if($users->count() === 0){
//            $this->error('No teachers found');
//            exit;
//        }
//
//        $obj = (object) [
//            'users' => 0,
//            'tests' => 0,
//            'answers'=> 0
//        ];
//
//        $users->each(function(User $user) use ($obj){
//
//            if($user->teacher()->count() == 0){
//                return;
//            }
//            $this->info(sprintf('Teacher %s %s %s:',$user->name_first, $user->suffix, $user->name));
//            $obj->users++;
//
//            $user->testtakes->each(function(TestTake $testTake) use ($obj) {
//                $this->output->write(sprintf('<info>  o Test (%d): %s...</info>',$testTake->getKey(),$testTake->test->name),false);
//                $questions = QuestionGatherer::getQuestionsOfTest($testTake->test->getkey(), true);
//                $obj->tests++;
//                $questionParentAr = [];
//                foreach($questions as $dottedId => $question){
//                    $idAr = explode('.',$dottedId);
//                    if(count($idAr) == 2) { // group question
//                        $questionParentAr[(int) $idAr[1]] = (int) $idAr[0];
//                    }
//                }
//                $obj->currentAnswers = 0;
//                $testTake->testParticipants->each(function(TestParticipant $tp) use ($questionParentAr, $obj){
//                    $tp->answers->each(function(Answer $a) use ($questionParentAr, $obj){
//                        if(array_key_exists($a->question_id, $questionParentAr)) {
//                            $apq = AnswerParentQuestion::firstOrCreate([
//                                'answer_id' => $a->getKey(),
//                                'group_question_id' => $questionParentAr[$a->question_id]
//                            ]);
//                            if($apq->wasRecentlyCreated){
//                                $obj->answers++;
//                                $obj->currentAnswers++;
//                            }
//                        }
//                    });
//                });
//                $this->info(sprintf('done adding %d records',$obj->currentAnswers));
//            });
//        });
        $duration = microtime(true) - $start;
        $this->info(sprintf('done, found %d teachers, checked %d test takes and added %d AnswerParentQuestion records in %f seconds ',$obj->users,$obj->tests, $obj->answers, $duration));
    }
}
