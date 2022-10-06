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
        if($this->scope !== $this->publishesTestsScope)
        {
            $this->setAttribute('scope', $this->publishesTestsScope);
            $this->setAttribute('author_id', $this->publishesTestsAuthor->getKey());
            $this->save();
        }
        TestAuthor::where('test_id', $this->getKey())->delete(); // we don't want to show the old author as it is a toetsenbakker probably
        TestAuthor::addAuthorToTest($this, $this->publishesTestsAuthor->getKey());
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

        if ($this->author_id != $this->publishesTestsAuthor->getKey()) {
            $this->author_id = $this->publishesTestsAuthor->getKey();
            $this->save();
            TestAuthor::where('test_id', $this->getKey())->delete(); // we don't want to show the old author as it is a toetsenbakker probably
            TestAuthor::addAuthorToTest($this, $this->publishesTestsAuthor->getKey());
        }
    }

    private function publishTestQuestions($questions): void
    {
        $questions->each(function ($question) {
            $question->setAttribute('scope', $this->publishesTestsScope);
            $question->save();
            QuestionAuthor::where('question_id', $question->getKey())->delete(); // we don't want to show the old author as it is a toetsenbakker probably
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