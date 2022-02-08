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


    private function handleExamPublishingQuestion()
    {
        if($this->allowExamQuestionPublished()){
            $this->getQuestionInstance()->setAttribute('scope', 'exam');
        }elseif($this->shouldUnpublishExamQuestion()){
            $this->unpublishExamQuestion();
        }
    }

    private function allowExamQuestionPublished()
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

    private function shouldUnpublishExamQuestion()
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

    public function hasNonPublishableExamSubject()
    {
        if($this->getQuestionInstance()->subject->name=='TLC Toetsenbakken'){
            return true;
        }
        if($this->hasNonPublishableExamSubjectDemo()){
            return true;
        }
        return false;
    }

    public function hasNonPublishableExamSubjectDemo()
    {
        if($this->getQuestionInstance()->subject->name=='Demovak'){
            return true;
        }
        return false;
    }

    private function unpublishExamQuestion()
    {
        $this->getQuestionInstance()->setAttribute('scope', 'not_exam');
    }

    private function examTestOfQuestionIsPublished()
    {
        foreach($this->testQuestions as $testQuestion){
            if($testQuestion->test->scope == 'exam'){
                return true;
            }
        }
        return false;
    }
}