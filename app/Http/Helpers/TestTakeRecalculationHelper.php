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
use tcCore\Lib\Answer\AnswerChecker;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class TestTakeRecalculationHelper
{

    protected $commandEnv = false;

    protected $updatedAnswers = 0;

    protected function hasCommandEnv()
    {
        return $this->commandEnv !== null;
    }

    public function __construct($commandEnv = null)
    {
        $this->commandEnv = $commandEnv;
    }

    public function resetUpdatedAnswers()
    {
        $this->updatedAnswers = 0;
        return $this;
    }

    public function getUpdatedAnswerCount()
    {
        return $this->updatedAnswers;
    }

    public function recalculateSystemRatingsForTestTake(TestTake $testTake, bool $dryRun = false)
    {
        $changed = false;
        $testTake->testParticipants->each(function(TestParticipant $tp) use (&$changed, $dryRun){
           if(AnswerChecker::checkAnswerOfParticipant($tp, true, $dryRun, $this->commandEnv)){
               $changed = true;
               $this->updatedAnswers++;
           }
        });
        return $changed;
    }

    public function hasTeacherRatingsForTestTake(TestTake $testTake)
    {
        $hasTeacherRatings = false;
        $testTake->testParticipants->each(function(TestParticipant $tp) use (&$hasTeacherRatings){
            $tp->answers->each(function(Answer $a) use (&$hasTeacherRatings){
                if($a->answerRatings()->where('type','TEACHER')->count() > 0){
                    $hasTeacherRatings = true;
                    return false;
                }
            });
            if($hasTeacherRatings === true){
                return false;
            }
        });
        return $hasTeacherRatings;
    }

}