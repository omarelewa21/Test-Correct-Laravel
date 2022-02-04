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

trait ExamSchoolTrait {

    private function allowExamPublished()
    {
        if(!Auth::user()->isInExamSchool()){
            return false;
        }
        if($this->hasNonPublishableExamSubject()){
            return false;
        }
        return true;
    }

    public function hasNonPublishableExamSubject()
    {
        if($this->subject->name=='TLC Toetsenbakken'){
            return true;
        }
        if($this->hasNonPublishableExamSubjectDemo()){
            return true;
        }
        return false;
    }

    public function hasNonPublishableExamSubjectDemo()
    {
        if($this->subject->name=='Demovak'){
            return true;
        }
        return false;
    }

    public function setExamTestParams()
    {
        $this->setAttribute('scope', 'exam');
        $this->setAttribute('abbreviation', 'EXAM');
        $authorUser = AuthorsController::getCentraalExamenAuthor();
        if($authorUser){
            $this->setAttribute('author_id', $authorUser->getKey());
        }
    }

    public function setExamParamsOnQuestionsOfTest()
    {
        if(get_class($this)!='Test'){
            throw new \Exception('illegal method for class');
        }
        $questions = $this->testQuestions->map(function($testQuestion){
            return $testQuestion->question->getQuestionInstance();
        });
        $questions->each(function($question){
            $question->setAttribute('scope', 'exam');
            $question->save();
            $authorUser = AuthorsController::getCentraalExamenAuthor()->getKey();
            if($authorUser) {
                QuestionAuthor::addAuthorToQuestion($question, $authorUser->getKey());
            }
        });
    }
}