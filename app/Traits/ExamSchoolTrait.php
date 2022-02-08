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

    private function handleExamPublishingTest()
    {
        if(get_class($this)!='tcCore\Test'){
            return;
        }
        if($this->allowExamPublished()){
            $this->setExamTestParams();
        }elseif($this->shouldUnpublishExamTest()){
            $this->unpublishExam();
        }
    }

    private function allowExamPublished()
    {
        if(!optional(Auth::user())->isInExamSchool()){
            return false;
        }
        if($this->hasNonPublishableExamSubject()){
            return false;
        }
        if(get_class($this)=='tcCore\Question'){
            return true;
        }
        if($this->abbreviation != 'CE' && $this->abbreviation != 'EXAM'){
            return false;
        }
        return true;
    }

    private function shouldUnpublishExamTest()
    {
        if(!optional(Auth::user())->isInExamSchool()){
            return false;
        }
        if($this->abbreviation != 'EXAM'){
            return true;
        }
        return false;
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
        $this->setExamParamsOnQuestionsOfTest();
    }

    private function unpublishExam()
    {
        $this->setAttribute('scope', 'not_exam');
        $this->setAttribute('abbreviation', 'NOT_EXAM');
    }

    public function setExamParamsOnQuestionsOfTest()
    {
        if(get_class($this)!='tcCore\Test'){
            return;
        }
        $questions = $this->testQuestions->map(function($testQuestion){
            return $testQuestion->question->getQuestionInstance();
        });
        $questions->each(function($question){
            $question->setAttribute('scope', 'exam');
            $question->save();
            $authorUser = AuthorsController::getCentraalExamenAuthor();
            if($authorUser) {
                QuestionAuthor::addAuthorToQuestion($question, $authorUser->getKey());
            }
        });
    }

    public function setUnpublishQuestionsOfTest()
    {
        if(get_class($this)!='tcCore\Test'){
            return;
        }
        $questions = $this->testQuestions->map(function($testQuestion){
            return $testQuestion->question->getQuestionInstance();
        });
        $questions->each(function($question){
            $question->setAttribute('scope', 'not_exam');
            $question->save();
        });
    }
}