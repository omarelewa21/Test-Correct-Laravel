<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 26/03/16
 * Time: 21:12
 */

namespace tcCore\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\QuestionAuthor;

trait ExamSchoolQuestionTrait {


    private function handleExamPublishingQuestion():void
    {
        if($this->allowExamQuestionPublished()){
            $this->getQuestionInstance()->setAttribute('scope', 'exam');
        }elseif($this->shouldUnpublishExamQuestion()){
            $this->unpublishExamQuestion();
        }
    }

    private function allowExamQuestionPublished():bool
    {
        if(!optional(Auth::user())->isInExamSchool()){
            return false;
        }
        if($this->hasNonPublishableExamSubject()){
            return false;
        }
        if(!$this->examTestOfQuestionIsPublished()){
            return false;
        }
        return true;
    }

    private function shouldUnpublishExamQuestion():bool
    {
        if(!optional(Auth::user())->isInExamSchool()){
            return false;
        }
        if($this->hasNonPublishableExamSubject()){
            return true;
        }
        if($this->examTestOfQuestionIsPublished()){
            return false;
        }
        return true;
    }

    private function hasNonPublishableExamSubject():bool
    {
        if($this->getQuestionInstance()->subject->name=='TLC Toetsenbakken'){
            return true;
        }
        if($this->hasNonPublishableExamSubjectDemo()){
            return true;
        }
        return false;
    }

    private function hasNonPublishableExamSubjectDemo():bool
    {
        if($this->getQuestionInstance()->subject->name=='Demovak'){
            return true;
        }
        return false;
    }

    private function unpublishExamQuestion():void
    {
        $this->getQuestionInstance()->setAttribute('scope', 'not_exam');
    }

    private function examTestOfQuestionIsPublished():bool
    {
        if($this->toggleTestQuestionsForScopeExamOnTest($this)){
            return true;
        }
        foreach($this->getQuestionInstance()->groupQuestionQuestions as $groupQuestionQuestion){
            if($this->toggleTestQuestionsForScopeExamOnTest($groupQuestionQuestion->groupQuestion->getQuestionInstance())){
                return true;
            }
        }
        return false;
    }

    private function toggleTestQuestionsForScopeExamOnTest($question):bool
    {
        foreach($question->testQuestions as $testQuestion){
            if(null !== $testQuestion->test && $testQuestion->test->scope == 'exam'&&!$testQuestion->test->is_system_test){
                return true;
            }
        }
        return false;
    }

}