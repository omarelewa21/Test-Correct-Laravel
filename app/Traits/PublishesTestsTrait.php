<?php

namespace tcCore\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestion;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\QuestionAuthor;
use tcCore\TestAuthor;


trait PublishesTestsTrait
{

    /**
     * 'customer_code' => []
     */
    private $publishesTestslookupTable;
    private $publishesTestsCustomerCode;

    private $publishesTestsAbbreviation = null;
    private $publishesTestsScope = null;
    private $publishesTestsAuthor = null;

    //old variables
    private $testPublishingType = null;
    private $testPublishingScope = null;

    private function getPublishesTestsTraitLookupTable()
    {
        return collect([
            config('custom.examschool_customercode')                => [
                'abbreviation' => 'EXAM',
                'scope'        => 'exam',
            ],
            config('custom.national_item_bank_school_customercode') => [
                'abbreviation' => 'LDT',
                'scope'        => 'ldt',
            ],
            config('custom.creathlon_school_customercode')          => [
                'abbreviation' => 'PUBLS',
                'scope'        => 'published_creathlon',
            ],
        ]);
    }

    private function initPublishesTestTraitProperties(): bool
    {
        $this->publishesTestslookupTable = $this->getPublishesTestsTraitLookupTable();

        $this->publishesTestsCustomerCode = optional(Auth::user())->schoolLocation->customer_code ?? false;

        if (!$this->publishesTestsCustomerCode) {
            return false;
        }
        if (!$this->publishesTestslookupTable->has($this->publishesTestsCustomerCode)) {
            return false;
        }
        $this->publishesTestsScope = $this->publishesTestslookupTable[$this->publishesTestsCustomerCode]['scope'];
        $this->publishesTestsAbbreviation = $this->publishesTestslookupTable[$this->publishesTestsCustomerCode]['abbreviation'];
        $this->publishesTestsAuthor = AuthorsController::getPublishableAuthorByCustomerCode($this->publishesTestsCustomerCode);

        return true;
    }

    //TODO Make test publishing dynamic.

    // Main components of a Publishable test:
    //  -- Abbreviation
    //  -- Scope
    //  -- author (of specific school from config, to set test author to) //$authorUser = AuthorsController::getNationalItemBankAuthor();

    // step 1: determine school of test (or break)
    // step 2: determine should publish or should unpublish
    // step 3: handle publishing or unpublishing

    //TODO make lookupTable with customer codes as key.

    //TODO make or edit feature tests //TestPublishingTest

    //Exam && NationalItemBank scopes:
    //  finished/published:     EXAM/exam  vs  LDT/ldt  vs Creathlon vs unlimited more others...
    //  not finished:           not_exam   vs  not_ldt

    private function handleTestPublishing(): void
    {
        if ($this->initPublishesTestTraitProperties() === false) {
            return;
        }
        if ($this->shouldPublishTest()) {
            $this->publishTest();
            return;
        }
        if ($this->shouldUnpublishTest()) {
            $this->unpublishTest();
            return;
        }
    }

    private function handlePublishingQuestionsOfTest(): void
    {
        if ($this->initPublishesTestTraitProperties() === false) {
            return;
        }

        if ($this->shouldPublishTestQuestions()) {
            $this->publishQuestionsOfTest();
            return;
        }
        if ($this->shouldUnpublishTestQuestions()) {
            $this->unpublishQuestionsOfTest();
            return;
        }

        return;
    }

    private function shouldPublishTest()
    {
        if ($this->hasNonPublishableTestSubject()) {
            return false;
        }
        if ($this->abbreviation != $this->publishesTestsAbbreviation) {
            return false;
        }
        return true;
    }

    private function shouldUnpublishTest()
    {
        if (optional(Auth::user())->schoolLocation->getKey() !== $this->owner_id) {
            return false;
        }
        if ($this->abbreviation != $this->publishesTestsAbbreviation) {
            return true;
        }
        return false;
    }

    private function shouldPublishTestQuestions(): bool
    {
        if ($this->scope == $this->publishesTestsScope) {
            return true;
        }
        return false;
    }

    private function shouldUnpublishTestQuestions(): bool
    {
        if (optional(Auth::user())->schoolLocation->getKey() !== $this->owner_id) {
            return false;
        }
        if ($this->scope == 'not_' . $this->publishesTestsScope) {
            return true;
        }
        return false;
    }

    private function hasNonPublishableTestSubject(): bool
    {
        if ($this->subject->name == 'TLC Toetsenbakken') {
            return true;
        }
        if ($this->hasNonPublishableTestSubjectDemo()) {
            return true;
        }
        return false;
    }

    private function hasNonPublishableTestSubjectDemo(): bool
    {
        if ($this->subject->name == 'Demovak') {
            return true;
        }
        return false;
    }

    private function publishTest(): void
    {
        $this->setAttribute('scope', $this->publishesTestsScope);
        $this->setAttribute('author_id', $this->publishesTestsAuthor->getKey());
    }

    private function unpublishTest(): void
    {
        $this->setAttribute('scope', 'not_' . $this->publishesTestsScope);
    }

    private function publishQuestionsOfTest(): void
    {
        $questions = $this->testQuestions->map(function ($testQuestion) {
            return $testQuestion->question->getQuestionInstance();
        });
        $this->publishTestQuestions($questions);
    }

    private function publishTestQuestions($questions): void
    {
        $questions->each(function ($question) {
            $question->setAttribute('scope', $this->publishesTestsScope);
            $question->save();
            QuestionAuthor::addAuthorToQuestion($question, $this->publishesTestsAuthor->getKey());
            if ($question->type == 'GroupQuestion') {
                $this->GroupQuestionRecursive($question, 'publishTestQuestions');
            }
        });
    }

    private function unpublishQuestionsOfTest(): void
    {
        $testQuestions = $this->testQuestions->map(function ($testQuestion) {
            return $testQuestion->question->getQuestionInstance();
        });
        $this->unpublishTestQuestions($testQuestions);
    }

    private function unpublishTestQuestions($questions): void
    {
        $questions->each(function ($question) {
            $question->setAttribute('scope', 'not_' . $this->publishesTestsScope);
            $question->save();
            if ($question->type == 'GroupQuestion') {
                $this->GroupQuestionRecursive($question, 'unpublishTestQuestions');
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