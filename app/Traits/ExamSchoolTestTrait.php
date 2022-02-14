<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 26/03/16
 * Time: 21:12
 */

namespace tcCore\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestion;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\QuestionAuthor;

trait ExamSchoolTestTrait {

    private function handleExamPublishingTest():void
    {
        if($this->allowExamPublished()){
            $this->setExamTestParams();
        }elseif($this->shouldUnpublishExamTest()){
            $this->unpublishExam();
        }
    }

    private function handleExamPublishingQuestionsOfTest():void
    {
        if($this->allowExamQuestionsPublished()){
            $this->setExamParamsOnQuestionsOfTest();
        }elseif($this->shouldUnpublishExamQuestionsOfTest()){
            $this->unpublishQuestionsOfTest();
        }
    }

    private function allowExamPublished():bool
    {
        if(!optional(Auth::user())->isInExamSchool()){
            return false;
        }
        if($this->hasNonPublishableExamSubject()){
            return false;
        }
        if($this->abbreviation != 'EXAM'){
            return false;
        }
        return true;
    }

    private function allowExamQuestionsPublished():bool
    {
        if(!optional(Auth::user())->isInExamSchool()){
            return false;
        }
        if($this->scope=='exam'){
            return true;
        }
        return false;
    }

    private function shouldUnpublishExamTest():bool
    {
        if(!optional(Auth::user())->isInExamSchool()){
            return false;
        }
        if($this->abbreviation != 'EXAM'){
            return true;
        }
        return false;
    }

    private function shouldUnpublishExamQuestionsOfTest():bool
    {
        if(!optional(Auth::user())->isInExamSchool()){
            return false;
        }
        if($this->scope != 'exam'){
            return true;
        }
        return false;
    }

    public function hasNonPublishableExamSubject():bool
    {
        if($this->subject->name=='TLC Toetsenbakken'){
            return true;
        }
        if($this->hasNonPublishableExamSubjectDemo()){
            return true;
        }
        return false;
    }

    public function hasNonPublishableExamSubjectDemo():bool
    {
        if($this->subject->name=='Demovak'){
            return true;
        }
        return false;
    }

    public function setExamTestParams():void
    {
        $this->setAttribute('scope', 'exam');
        $this->setAttribute('abbreviation', 'EXAM');
        $authorUser = AuthorsController::getCentraalExamenAuthor();
        if(!is_null($authorUser)){
            $this->setAttribute('author_id', $authorUser->getKey());
        }
    }

    private function unpublishExam():void
    {
        $this->setAttribute('scope', 'not_exam');
        $this->setAttribute('abbreviation', 'NOTCE');
    }

    public function setExamParamsOnQuestionsOfTest():void
    {
        $questions = $this->testQuestions->map(function($testQuestion){
            return $testQuestion->question->getQuestionInstance();
        });
        $this->setExamParamsOnQuestions($questions);
    }

    private function setExamParamsOnQuestions($questions):void
    {
        $questions->each(function($question){
            $question->setAttribute('scope', 'exam');
            $question->save();
            $authorUser = AuthorsController::getCentraalExamenAuthor();
            if(!is_null($authorUser)) {
                QuestionAuthor::addAuthorToQuestion($question, $authorUser->getKey());
            }
            if($question->type == 'GroupQuestion'){
                $this->groupQuestionRecursive($question,'setExamParamsOnQuestions');
            }
        });
    }

    public function unpublishQuestionsOfTest():void
    {
        $questions = $this->testQuestions->map(function($testQuestion){
            return $testQuestion->question->getQuestionInstance();
        });
        $this->unpublishQuestions($questions);
    }

    private function unpublishQuestions($questions):void
    {
        $questions->each(function($question){
            $question->setAttribute('scope', 'not_exam');
            $question->save();
            if($question->type == 'GroupQuestion'){
                $this->groupQuestionRecursive($question,'unpublishQuestions');
            }
        });
    }

    private function groupQuestionRecursive($question,$functionCall):void
    {
        $groupQuestion = GroupQuestion::find($question->getKey());
        $subQuestions = $groupQuestion->groupQuestionQuestions->map(function($groupQuestionQuestion){
            return $groupQuestionQuestion->question->getQuestionInstance();
        });
        $this->$functionCall($subQuestions);
    }
}