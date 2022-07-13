<?php

namespace tcCore\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestion;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\QuestionAuthor;
use tcCore\TestAuthor;

trait NationalItemBankSchoolTestTrait {

    //Exam vs NationalItemBank:
    //  finished/published:     EXAM/exam  vs  LDT/ldt
    //  not finished:           not_exam   vs  not_ldt

    private function handleNationalItemBankTestPublishing():void
    {
        if($this->allowNationalItemBankTestPublished()){
            $this->setNationalItemBankTestParams();
        }elseif($this->shouldUnpublishNationalItemBankTest()){
            $this->unpublishNationalItemBankTest();
        }
    }

    private function handleNationalItemBankPublishingQuestionsOfTest():void
    {
        if($this->allowNationalItemBankTestQuestionsPublished()){
            $this->setNationalItemBankTestParamsOnQuestionsOfTest();
        }elseif($this->shouldUnpublishNationalItemBankQuestionsOfTest()){
            $this->unpublishQuestionsOfNationalItemBankTest();
        }
    }

    private function allowNationalItemBankTestPublished():bool
    {
        if(!optional(Auth::user())->isInNationalItemBankSchool()){
            return false;
        }
        if($this->hasNonPublishableNationalItemBankTestSubject()){
            return false;
        }
        if($this->abbreviation != 'LDT'){
            return false;
        }
        return true;
    }

    private function allowNationalItemBankTestQuestionsPublished():bool
    {
        if(!optional(Auth::user())->isInNationalItemBankSchool()){
            return false;
        }
        if($this->scope=='ldt'){
            return true;
        }
        return false;
    }

    private function shouldUnpublishNationalItemBankTest():bool
    {
        if(!optional(Auth::user())->isInNationalItemBankSchool()){
            return false;
        }
        if($this->abbreviation != 'LDT'){
            return true;
        }
        return false;
    }

    private function shouldUnpublishNationalItemBankQuestionsOfTest():bool
    {
        if(!optional(Auth::user())->isInNationalItemBankSchool()){
            return false;
        }
        if($this->scope != 'ldt'){
            return true;
        }
        return false;
    }

    //duplicate
    public function hasNonPublishableNationalItemBankTestSubject():bool
    {
        if($this->subject->name=='TLC Toetsenbakken'){
            return true;
        }
        if($this->hasNonPublishableNationalItemBankTestSubjectDemo()){
            return true;
        }
        return false;
    }

    //duplicate
    public function hasNonPublishableNationalItemBankTestSubjectDemo():bool
    {
        if($this->subject->name=='Demovak'){
            return true;
        }
        return false;
    }

    public function setNationalItemBankTestParams():void
    {
        $this->setAttribute('scope', 'ldt');
        $authorUser = AuthorsController::getNationalItemBankAuthor();
        if(!is_null($authorUser)){
            $this->setAttribute('author_id', $authorUser->getKey());
        }
    }

    public function setNationalItemBankTestAuthor():void
    {
        $authorUser = AuthorsController::getNationalItemBankAuthor();
        if(!is_null($authorUser)){
            TestAuthor::addAuthorToTest($this, $authorUser->getKey());
        }

    }

    private function unpublishNationalItemBankTest():void
    {
        $this->setAttribute('scope', 'not_ldt');
    }

    public function setNationalItemBankTestParamsOnQuestionsOfTest():void
    {
        $questions = $this->testQuestions->map(function($testQuestion){
            return $testQuestion->question->getQuestionInstance();
        });
        $this->setNationalItemBankTestParamsOnQuestions($questions);
    }

    private function setNationalItemBankTestParamsOnQuestions($questions):void
    {
        $questions->each(function($question){
            $question->setAttribute('scope', 'ldt');
            $question->save();
            $authorUser = AuthorsController::getNationalItemBankAuthor();
            if(!is_null($authorUser)) {
                QuestionAuthor::addAuthorToQuestion($question, $authorUser->getKey());
            }
            if($question->type == 'GroupQuestion'){
                $this->nationalGroupQuestionRecursive($question,'setNationalItemBankTestParamsOnQuestions');
            }
        });
    }

    //semi duplicate (unpublish call)
    public function unpublishQuestionsOfNationalItemBankTest():void
    {
        $questions = $this->testQuestions->map(function($testQuestion){
            return $testQuestion->question->getQuestionInstance();
        });
        $this->unpublishNationalItemBankTestQuestions($questions);
    }

    private function unpublishNationalItemBankTestQuestions($questions):void
    {
        $questions->each(function($question){
            $question->setAttribute('scope', 'not_ldt');
            $question->save();
            if($question->type == 'GroupQuestion'){
                $this->nationalGroupQuestionRecursive($question,'unpublishNationalItemBankTestQuestions');
            }
        });
    }

    //duplicate
    private function nationalGroupQuestionRecursive($question,$functionCall):void
    {
        $groupQuestion = GroupQuestion::find($question->getKey());
        $subQuestions = $groupQuestion->groupQuestionQuestions->map(function($groupQuestionQuestion){
            return $groupQuestionQuestion->question->getQuestionInstance();
        });
        $this->$functionCall($subQuestions);
    }
}