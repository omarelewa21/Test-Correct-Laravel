<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class AnswerParentQuestionsHelper
{

    protected $commandEnv = false;

    protected function hasCommandEnv()
    {
        return $this->commandEnv !== null;
    }

    public function __construct($commandEnv = null)
    {
        $this->commandEnv = $commandEnv;
    }

    public function fixAnswerParentQuestionsPerSchoolLocation(SchoolLocation $schoolLocation)
    {
        $obj = (object) [
            'users' => 0,
            'tests' => 0,
            'answers'=> 0
        ];

        $users = User::whereNotNull('abbreviation')->where('school_location_id',$schoolLocation->getKey())->get();
        if($users->count() > 0){
            $users->each(function(User $user) use ($obj) {
                if($this->hasCommandEnv()){
                    $this->commandEnv->toInfo(sprintf('Teacher %s %s %s:',$user->name_first, $user->suffix, $user->name));
                }
                $data = $this->fixAnswerParentQuestionsPerUser($user);
                $obj->tests += $data->tests;
                $obj->answers += $data->answers;
            });
            if($this->hasCommandEnv()){
                $user = null;
            }
        }
        if($this->hasCommandEnv()){
            $users = null;
        }
        if($this->hasCommandEnv()){
            $schoolLocation = null;
        }
        return $obj;
    }

    public function fixAnswerParentQuestionsPerUser(User $user)
    {
        $obj = (object) [
            'tests' => 0,
            'answers'=> 0
        ];

        if($user->teacher()->count() == 0){
            if($this->hasCommandEnv()){
                $this->commandEnv->toInfo(sprintf('This doesn`t seem to be a teacher (%s %s %s)',$user->name_first, $user->name_suffix, $user->name));
                return $obj;
            }
            else {
                return $obj;
            }
        }


        $user->testtakes->each(function(TestTake $testTake) use ($obj) {
            $obj->tests++;
            $obj->answers += $this->fixAnswerParentQuestionsPerTestTake($testTake);
        });
        if($this->hasCommandEnv()){
            $user = null;
        }
        return $obj;
    }

    public function fixAnswerParentQuestionsPerTestTake(TestTake $testTake)
    {
        if($this->hasCommandEnv()){
            $this->commandEnv->toOutput(sprintf('<info>  o Test (%d): %s...</info>',$testTake->getKey(),$testTake->test->name),false);
        }
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
            $count += $this->fixAnswerParentQuestionsPerTestParticipant($tp, $questionParentAr);
            if($this->hasCommandEnv()){
                $tp = null;
            }
        });

        if($this->hasCommandEnv()){
            if($count > 0){
                $this->commandEnv->toError(sprintf('done adding %d records',$count));
            }
            else{
                $this->commandEnv->toInfo(sprintf('checked, no records added'));
            }
        }
        if($this->hasCommandEnv()){
            $testTake = null;
        }
        return $count;
    }

    public function fixAnswerParentQuestionsPerTestParticipant(TestParticipant $tp, $questionParentAr = null)
    {
        if($questionParentAr === null) {
            $questions = QuestionGatherer::getQuestionsOfTest($tp->testTake->test->getkey(), true);
            $questionParentAr = [];
            foreach ($questions as $dottedId => $question) {
                $idAr = explode('.', $dottedId);
                if (count($idAr) == 2) { // group question
                    $questionParentAr[(int)$idAr[1]] = (int)$idAr[0];
                }
            }
        }

        $count = 0;
        $tp->answers->each(function(Answer $a) use ($questionParentAr, &$count){
            if(array_key_exists($a->question_id, $questionParentAr)) {
                $apq = AnswerParentQuestion::firstOrCreate(
                    [
                    'answer_id' => $a->getKey(),
                    'group_question_id' => $questionParentAr[$a->question_id],
                    ],
                    ['level' => 1]
                );
                if($apq->wasRecentlyCreated){
                    $count++;
                }
            }
        });
        if($this->hasCommandEnv()){
            $tp = null;
        }
        return $count;
    }
}