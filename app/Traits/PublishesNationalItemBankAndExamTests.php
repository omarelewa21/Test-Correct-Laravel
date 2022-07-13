<?php

namespace tcCore\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestion;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\QuestionAuthor;
use tcCore\TestAuthor;


trait PublishesNationalItemBankAndExamTests //publishesNationalAndExamTestTrait
{

    private $testPublishingType = null;
    private $testPublishingScope = null;

    //Exam && NationalItemBank scopes:
    //  finished/published:     EXAM/exam  vs  LDT/ldt
    //  not finished:           not_exam   vs  not_ldt

    private function handleTestPublishing(): void
    {
        // national item bank publishing
        if ($this->allowNationalItemBankTestPublished()) {
            $this->setPublishingTestType('national', true);
            $this->setTestParams();
        } elseif ($this->shouldUnpublishNationalItemBankTest()) {
            $this->setPublishingTestType('national', false);
            $this->unpublishTest();
        }

        // exam publishing
        if ($this->allowExamPublished()) {
            $this->setPublishingTestType('exam', true);
            $this->setTestParams();
        } elseif ($this->shouldUnpublishExamTest()) {
            $this->setPublishingTestType('exam', false);
            $this->unpublishTest();
        }
    }

    private function handlePublishingQuestionsOfTest(): void
    {
        if ($this->allowNationalItemBankTestQuestionsPublished()) {
            $this->setPublishingTestType('national', true);
            $this->setTestParamsOnQuestionsOfTest();
        } elseif ($this->shouldUnpublishQuestionsOfNationalItemBankTest()) {
            $this->setPublishingTestType('national', false);
            $this->unpublishQuestionsOfTest();
        }

        if ($this->allowExamQuestionsPublished()) {
            $this->setPublishingTestType('exam', true);
            $this->setTestParamsOnQuestionsOfTest();
        } elseif ($this->shouldUnpublishExamQuestionsOfTest()) {
            $this->setPublishingTestType('exam', false);
            $this->unpublishQuestionsOfTest();
        }
    }

    private function setPublishingTestType(string $testType, bool $publish) : void
    {
        switch($testType)
        {
            case 'national':
                $this->testPublishingScope = $publish ? 'ldt' : 'not_ldt';
                $this->testPublishingType = 'national';
                break;
            case 'exam':
                $this->testPublishingScope = $publish ? 'exam' : 'not_exam';
                $this->testPublishingType = 'exam';
                break;
        }
    }


    private function allowNationalItemBankTestPublished(): bool
    {
        if (!optional(Auth::user())->isInNationalItemBankSchool()) {
            return false;
        }
        if ($this->hasNonPublishableTestSubject()) {
            return false;
        }
        if ($this->abbreviation != 'LDT') {
            return false;
        }
        return true;
    }

    private function allowNationalItemBankTestQuestionsPublished(): bool
    {
        if (!optional(Auth::user())->isInNationalItemBankSchool()) {
            return false;
        }
        if ($this->scope == 'ldt') {
            return true;
        }
        return false;
    }

    private function shouldUnpublishNationalItemBankTest(): bool
    {
        if (!optional(Auth::user())->isInNationalItemBankSchool()) {
            return false;
        }
        if ($this->abbreviation != 'LDT') {
            return true;
        }
        return false;
    }

    private function shouldUnpublishQuestionsOfNationalItemBankTest(): bool
    {
        if (!optional(Auth::user())->isInNationalItemBankSchool()) {
            return false;
        }
        if ($this->scope != 'ldt') {
            return true;
        }
        return false;
    }

    private function allowExamPublished(): bool
    {
        if (!optional(Auth::user())->isInExamSchool()) {
            return false;
        }
        if ($this->hasNonPublishableTestSubject()) {
            return false;
        }
        if ($this->abbreviation != 'EXAM') {
            return false;
        }
        return true;
    }

    private function allowExamQuestionsPublished(): bool
    {
        if (!optional(Auth::user())->isInExamSchool()) {
            return false;
        }
        if ($this->scope == 'exam') {
            return true;
        }
        return false;
    }

    private function shouldUnpublishExamTest(): bool
    {
        if (!optional(Auth::user())->isInExamSchool()) {
            return false;
        }
        if ($this->abbreviation != 'EXAM') {
            return true;
        }
        return false;
    }

    private function shouldUnpublishExamQuestionsOfTest(): bool
    {
        if (!optional(Auth::user())->isInExamSchool()) {
            return false;
        }
        if ($this->scope != 'exam') {
            return true;
        }
        return false;
    }

    public function hasNonPublishableTestSubject(): bool
    {
        if ($this->subject->name == 'TLC Toetsenbakken') {
            return true;
        }
        if ($this->hasNonPublishableTestSubjectDemo()) {
            return true;
        }
        return false;
    }

    public function hasNonPublishableTestSubjectDemo(): bool
    {
        if ($this->subject->name == 'Demovak') {
            return true;
        }
        return false;
    }

    public function setTestParams(): void
    {
        $authorUser = null;
        $this->setAttribute('scope', $this->testPublishingScope);

        switch ($this->testPublishingType) {
            case 'national':
                $authorUser = AuthorsController::getNationalItemBankAuthor();
                break;
            case 'exam':
                $authorUser = AuthorsController::getCentraalExamenAuthor();
                break;
        }

        if (!is_null($authorUser)) {
            $this->setAttribute('author_id', $authorUser->getKey());
        }
    }

    //todo: method is unused? author set through TestAuthor
    // TestAuthor::addExamAuthorToTest($test);
    // TestAuthor::addNationalItemBankAuthorToTest($test);
    public function setTestAuthor($testType): void
    {
        $authorUser = null;
        switch ($testType) {
            case 'national':
                $authorUser = AuthorsController::getNationalItemBankAuthor();
                break;
            case 'exam':
                $authorUser = AuthorsController::getCentraalExamenAuthor();
                break;
        }
        if (!is_null($authorUser)) {
            TestAuthor::addAuthorToTest($this, $authorUser->getKey());
        }

    }

    private function unpublishTest(): void
    {
        $this->setAttribute('scope', $this->testPublishingScope);
    }

    public function setTestParamsOnQuestionsOfTest(): void
    {
        $questions = $this->testQuestions->map(function ($testQuestion) {
            return $testQuestion->question->getQuestionInstance();
        });
        $this->setTestParamsOnQuestions($questions);
    }

    private function setTestParamsOnQuestions($questions): void
    {
        switch ($this->testPublishingType) {
            case 'national':
                $authorUser = AuthorsController::getNationalItemBankAuthor();
                break;
            case 'exam':
                $authorUser = AuthorsController::getCentraalExamenAuthor();
                break;
        }

        $questions->each(function ($question) use ($authorUser) {
            $question->setAttribute('scope', $this->testPublishingScope);
            $question->save();
            if (!is_null($authorUser)) {
                QuestionAuthor::addAuthorToQuestion($question, $authorUser->getKey());
            }
            if ($question->type == 'GroupQuestion') {
                $this->GroupQuestionRecursive($question, 'setTestParamsOnQuestions');
            }
        });
    }

    public function unpublishQuestionsOfTest(): void
    {
        $questions = $this->testQuestions->map(function ($testQuestion) {
            return $testQuestion->question->getQuestionInstance();
        });
        $this->unpublishNationalItemBankTestQuestions($questions);
    }

    private function unpublishNationalItemBankTestQuestions($questions): void
    {
        $questions->each(function ($question) {
            $question->setAttribute('scope', $this->testPublishingScope);
            $question->save();
            if ($question->type == 'GroupQuestion') {
                $this->GroupQuestionRecursive($question, 'unpublishNationalItemBankTestQuestions');
            }
        });
    }

    private function GroupQuestionRecursive($question, $functionCall): void
    {
        $groupQuestion = GroupQuestion::find($question->getKey());
        $subQuestions = $groupQuestion->groupQuestionQuestions->map(function ($groupQuestionQuestion) {
            return $groupQuestionQuestion->question->getQuestionInstance();
        });
        $this->$functionCall($subQuestions);
    }
}