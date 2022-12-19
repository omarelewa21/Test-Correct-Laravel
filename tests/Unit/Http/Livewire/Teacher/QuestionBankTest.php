<?php

namespace Tests\Unit\Http\Livewire\Teacher;

use Database\Seeders\NationalItemBankShortSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\Attainment;
use tcCore\Exceptions\QuestionException;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithSmallNationalItemBank;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Livewire\Teacher\QuestionBank;
use tcCore\Question;
use tcCore\QuestionAttainment;
use tcCore\Test;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimpleWithSmallNationalItemBank::class;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = ScenarioLoader::get('user');
        $this->actingAs($this->user);
        ActingAsHelper::getInstance()->setUser($this->user);
    }


    public function test_question_scope_filtered_returns_personal_dataset_with_filters_for_d1()
    {
        $this->actingAs($this->user);
        $cleanDBExpectedCount = 0;
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
        $this->actingAs($this->user);
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
        $nationalItemBankFilters = [
            'base_subject_id' => [1],
            'source'          => 'national',
        ];

        $questions = Question::publishedFiltered($nationalItemBankFilters);

        $this->assertEquals(1, $questions->count());
    }

//    public function test_question_scope_published_filtered_returns_national_dataset_without_filters_for_d1()
//    {
//        $this->actingAs($this->getTeacherOne());
//        $cleanDBExpectedCount = Question::whereIn('scope', Test::NATIONAL_ITEMBANK_SCOPES)->count();
//
//        $questions = Question::publishedFiltered();
//
//        $this->assertEquals($questions->count(), $cleanDBExpectedCount);
//    }

    public function test_question_scope_published_filtered_throws_exception_when_filtering_on_base_subject_id_that_is_not_in_current_school_year_for_d1()
    {
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
        $nationalItemBankFilters = [
            'source' => 'national',
        ];

        $questions = Question::publishedFiltered($nationalItemBankFilters);

        $this->assertEquals(1, $questions->count());
    }

    /**
     * @dataProvider educationLevelProvider
     */
    public function test_question_scope_published_filtered_returns_national_dataset_with_education_level_filter_for_d1($educationLevelIds, $expectedCount)
    {
        $nationalItemBankFilters = [
            'education_level_id' => $educationLevelIds,
            'source'             => 'national',
        ];

        $questions = Question::publishedFiltered($nationalItemBankFilters);

        $this->assertEquals($expectedCount, $questions->count());
    }


    public function educationLevelProvider()
    {
        return [
            [[1], 1],
            [[2], 0],
            [[1, 2], 1],
        ];
    }

    public function test_can_add_question_to_test_via_question_bank()
    {
        $question = $this->createOpenQuestion();
        $this->assertInstanceOf(Question::class, $question);

        $test = Test::find(2);
        $testQuestionCount = $test->testQuestions()->count();

        Livewire::withQueryParams(['testId' => $test->uuid, 'testQuestionId' => ''])
            ->test(QuestionBank::class)
            ->call('handleCheckboxClick', $question->getQuestionInstance()->uuid)
            ->assertDispatchedBrowserEvent('question-added');

        $this->assertGreaterThan($testQuestionCount, $test->testQuestions->count());
    }

    public function test_question_added_from_national_tab_creates_clean_copy_of_question()
    {
        $test = Test::find(2);
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

        $newQuestion = Question::orderByDesc('id')->first();

        $this->assertGreaterThan($testQuestionCount, $test->testQuestions()->count());
        $this->assertGreaterThan($totalQuestionCount, Question::count());

        $this->assertEquals('ldt', $questionToDuplicate->getQuestionInstance()->scope);
        $this->assertTrue(!!$questionToDuplicate->getQuestionInstance()->add_to_database);
        $this->assertFalse(!!$questionToDuplicate->getQuestionInstance()->add_to_database_disabled);
        $this->assertNull($newQuestion->scope);
        $this->assertNull($newQuestion->derived_question_id);
        $this->assertFalse(!!$newQuestion->add_to_database);
        $this->assertTrue(!!$newQuestion->add_to_database_disabled);
    }


    /**
     * @test
     * @group ignore
     */
    public function question_added_from_national_tab_creates_clean_copy_of_question_with_attainments()
    {
        //Base setup

        $test = Test::find(2);

        //Get Question and Attainment to add to question
        $nationalItemBankFilters = [
            'base_subject_id' => [1],
            'source'          => 'national',
        ];
        $nationalItemBankQuestion = Question::publishedFiltered($nationalItemBankFilters)->where('type', '<>', 'InfoscreenQuestion')->first();
        $baseSubjectId = $nationalItemBankQuestion->subject()->value('base_subject_id');
        $attainmentToAdd = Attainment::where('base_subject_id', $baseSubjectId)->first();

        //Verify the question has no attainments yet
        $this->assertNull(QuestionAttainment::whereQuestionId($nationalItemBankQuestion->getKey())->first());;

        //Attach the attainment to the question
        $nationalItemBankQuestion->questionAttainments()->create([
            'attainment_id' => $attainmentToAdd->getKey()
        ]);

        //Verify the question now has the attainment attached
        $this->assertNotNull(QuestionAttainment::whereQuestionId($nationalItemBankQuestion->getKey())->first());;
        $this->assertNotEmpty($nationalItemBankQuestion->getQuestionInstance()->attainments);
        $this->assertEquals($nationalItemBankQuestion->getQuestionInstance()->attainments->first()->getKey(), $attainmentToAdd->getKey());

        //Duplicate the question via the QuestionBank
        Livewire::withQueryParams(['testId' => $test->uuid, 'testQuestionId' => ''])
            ->test(QuestionBank::class)
            ->call('handleCheckboxClick', $nationalItemBankQuestion->getQuestionInstance()->uuid)
            ->assertDispatchedBrowserEvent('question-added');

        $newQuestion = Question::latest()->first();

        //Verify the new question has the attainment aswel;
        $this->assertNotEmpty($newQuestion->getQuestionInstance()->attainments);
        $this->assertEquals($newQuestion->getQuestionInstance()->attainments->first()->getKey(), $attainmentToAdd->getKey());
    }

    /**
     * @test
     * @group ignore
     */
    public function can_add_sub_question_to_test_via_question_bank_with_published_status_of_test()
    {
        $this->actingAs($this->user);

        $subQuestion = GroupQuestionQuestion::whereGroupQuestionId(14)->first()->question;
        $this->assertInstanceOf(Question::class, $subQuestion);
        $this->assertTrue($subQuestion->isPublished());

        $test = FactoryTest::create()->getTestModel();
        $this->assertTrue($test->isDraft());

        $testQuestionCount = $test->testQuestions()->count();

        $this->actingAs($this->getTeacherOne());
        Livewire::withQueryParams(['testId' => $test->uuid, 'testQuestionId' => ''])
            ->test(QuestionBank::class)
            ->call('handleCheckboxClick', $subQuestion->getQuestionInstance()->uuid)
            ->assertDispatchedBrowserEvent('question-added');

        $newQuestion = Question::latest()->first();

        $this->assertNotEquals($subQuestion->getKey(), $newQuestion->getKey());
        $this->assertTrue($newQuestion->isDraft());
    }
}
