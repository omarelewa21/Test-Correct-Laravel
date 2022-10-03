<?php

namespace Tests\Unit\Http\Livewire\Teacher;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Livewire\Teacher\QuestionBank;
use tcCore\Question;
use tcCore\Test;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
    use DatabaseTransactions;

    public function test_question_scope_filtered_returns_personal_dataset_with_filters_for_d1()
    {
        $this->actingAs($this->getTeacherOne());
        $cleanDBExpectedCount = 8;
        $personalFilters = [
            'subject_id'     => [1],
            'source'         => 'me',
            'is_subquestion' => false
        ];

        $questions = Question::filtered($personalFilters);

        $this->assertEquals($questions->count(), $cleanDBExpectedCount);
    }

    public function test_question_scope_filtered_returns_school_location_dataset_with_filters_for_d1()
    {
        $this->actingAs($this->getTeacherOne());
        $cleanDBExpectedCount = 0;
        $schoolLocationFilters = [
            'subject_id'     => [1],
            'source'         => 'schoolLocation',
            'is_subquestion' => false
        ];

        $questions = Question::filtered($schoolLocationFilters);

        $this->assertEquals($questions->count(), $cleanDBExpectedCount);
    }

    public function test_question_scope_published_filtered_returns_national_dataset_with_filters_for_d1()
    {
        $this->actingAs($this->getTeacherOne());
        $cleanDBExpectedCount = 11;

        $nationalItemBankFilters = [
            'base_subject_id' => [1],
            'source'          => 'national',
        ];

        $questions = Question::publishedFiltered($nationalItemBankFilters);

        $this->assertEquals($questions->count(), $cleanDBExpectedCount);
    }

    public function test_question_scope_published_filtered_returns_national_dataset_without_filters_for_d1()
    {
        $this->actingAs($this->getTeacherOne());
        $cleanDBExpectedCount = Question::whereIn('scope', Test::NATIONAL_ITEMBANK_SCOPES)->count();

        $questions = Question::publishedFiltered();

        $this->assertEquals($questions->count(), $cleanDBExpectedCount);
    }

    public function test_question_scope_published_filtered_throws_exception_when_filtering_on_base_subject_id_that_is_not_in_current_school_year_for_d1()
    {
        $this->actingAs($this->getTeacherOne());
        $this->expectException(QuestionException::class);
        $this->expectExceptionMessage('Cannot filter on base subjects not being given in the current period.');

        $nationalItemBankFilters = [
            'base_subject_id' => [5],
            'source'          => 'national',
        ];

        $questions = Question::publishedFiltered($nationalItemBankFilters)->get();
    }

    public function test_question_scope_published_filtered_returns_national_dataset_with_only_source_filter_for_d1()
    {
        $this->actingAs($this->getTeacherOne());
        $cleanDBExpectedCount = 22;

        $nationalItemBankFilters = [
            'source' => 'national',
        ];

        $questions = Question::publishedFiltered($nationalItemBankFilters);

        $this->assertEquals($questions->count(), $cleanDBExpectedCount);
    }

    public function test_question_scope_published_filtered_returns_national_dataset_with_education_level_filter_for_d1()
    {
        $this->actingAs($this->getTeacherOne());
        $cleanDBExpectedCount = 22;

        $nationalItemBankFilters = [
            'education_level_id' => [1],
            'source'             => 'national',
        ];

        $questions = Question::publishedFiltered($nationalItemBankFilters);

        $this->assertEquals($questions->count(), $cleanDBExpectedCount);
    }

    public function test_can_add_question_to_test_via_question_bank()
    {
        $this->actingAs($this->getTeacherOne());

        $question = $this->createOpenQuestion();
        $this->assertInstanceOf(Question::class, $question);

        $test = Test::find(3);
        $testQuestionCount = $test->testQuestions()->count();


        Livewire::withQueryParams(['testId' => $test->uuid, 'testQuestionId' => ''])
            ->test(QuestionBank::class)
            ->call('handleCheckboxClick', $question->getQuestionInstance()->uuid)
            ->assertDispatchedBrowserEvent('question-added');

        $this->assertGreaterThan($testQuestionCount, $test->testQuestions->count());
    }

    public function test_question_added_from_national_tab_creates_clean_copy_of_question()
    {
        $this->actingAs($this->getTeacherOne());
        $test = Test::find(3);
        $testQuestionCount = $test->testQuestions()->count();
        $totalQuestionCount = Question::count();

        $nationalItemBankFilters = [
            'base_subject_id' => [1],
            'source'          => 'national',
        ];
        $questionToDuplicate = Question::publishedFiltered($nationalItemBankFilters)->first();

        Livewire::withQueryParams(['testId' => $test->uuid, 'testQuestionId' => ''])
            ->test(QuestionBank::class)
            ->call('handleCheckboxClick', $questionToDuplicate->getQuestionInstance()->uuid)
            ->assertDispatchedBrowserEvent('question-added');

        $newQuestion = Question::latest()->first();

        $this->assertGreaterThan($testQuestionCount, $test->testQuestions()->count());
        $this->assertGreaterThan($totalQuestionCount, Question::count());

        $this->assertEquals($questionToDuplicate->getQuestionInstance()->scope, 'ldt');
        $this->assertTrue(!!$questionToDuplicate->getQuestionInstance()->add_to_database);
        $this->assertFalse(!!$questionToDuplicate->getQuestionInstance()->add_to_database_disabled);

        $this->assertNull($newQuestion->scope);
        $this->assertNull($newQuestion->derived_question_id);
        $this->assertFalse(!!$newQuestion->add_to_database);
        $this->assertTrue(!!$newQuestion->add_to_database_disabled);
    }
}