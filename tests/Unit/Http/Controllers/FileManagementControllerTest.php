<?php

namespace Tests\Unit\Http\Controllers;

use FontLib\EOT\File;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use tcCore\Console\Commands\duplicateTestTake;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithTwoQuestions;
use tcCore\FileManagement;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Controllers\FileManagementController;
use tcCore\Http\Requests\DuplicateFileManagementTestRequest;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Period;
use tcCore\Question;
use tcCore\QuestionAuthor;
use tcCore\Test;
use tcCore\TestAuthor;
use tcCore\TestQuestion;
use tcCore\User;
use Tests\TestCase;

class FileManagementControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Duplication of test from Toetsenbakker schoollocation to FileManagement author schoollocation
     *
     * Asserts the following:
     * - test has been duplicated
     * - test->author_id === fileManagement->user_id
     * - test->owner_id === fileManagement->schoolLocation_id
     * - questions have been duplicated
     * - questions->add_to_database_disbled === true
     * - questions->owner_id === fileManagement->schoolLocation->id
     * - questions->derived_question_id is no longer NULL
     * - TestAuthors have been removed and replaced
     * - QuestionAuthors have been removed and replaced
     *
     *
     * @test
     */
    public function testDuplicateTestToSchool()
    {
        //login as account manager user
        Auth::loginUsingId(520);

        $fileManagement = $this->createNewFileManagementAndTest();
        Auth::loginUsingId(520);

        $test = $fileManagement->test;

        $startTestCount = Test::count();
        $startTestQuestionsCount = TestQuestion::count();
        $startQuestionsCount = Question::count();

        $start_school_location = $test->owner_id; //2
        $start_author = $test->author_id;
        $start_period = $test->period_id;
        $start_subject = $test->subject_id;

        //assert 'add_to_database_disabled' is false for all questions
        $questionPropertiesToCheck = $this->getQuestionPropertiesToCheck($test);
        $originalQuestionsAddToDatabaseDisabledTrueCount = $questionPropertiesToCheck->reduce(fn($carry, $questionProperties) => $carry + ($questionProperties['add_to_database_disabled'] === true ? 1 : 0));
        $this->assertEquals(0, $originalQuestionsAddToDatabaseDisabledTrueCount);
        $originalQuestionOwnerIdCorrectCount = $questionPropertiesToCheck->reduce(fn($carry, $questionProperties) => $carry + ($questionProperties['owner_id'] === $fileManagement->schoolLocation->getKey() ? 1 : 0));
        $this->assertEquals(0, $originalQuestionOwnerIdCorrectCount);
        $originalQuestionDerivedQuestionIdNotNullCount = $questionPropertiesToCheck->reduce(fn($carry, $questionProperties) => $carry + ($questionProperties['derived_question_id'] !== null ? 1 : 0));
        $this->assertEquals(0, $originalQuestionDerivedQuestionIdNotNullCount);

        //request validates if auth user is an "Account Manager"
        (new FileManagementController())->duplicateTestToSchool(new DuplicateFileManagementTestRequest(), $fileManagement);

        $createdTest = Test::orderByDesc('created_at')->first();

        //assert properties of the original test are changed for the duplicate
        $this->assertNotEquals($start_author, $createdTest->author_id);
        $this->assertNotEquals($start_school_location, $createdTest->owner_id);

        //assert all parts were duplicated (and not existing questions linked to a new test)
        $this->assertGreaterThan($startTestCount, Test::count());
        $this->assertGreaterThan($startTestQuestionsCount, TestQuestion::count());
        $this->assertGreaterThan($startQuestionsCount, Question::count());

        //assert 'add_to_database_disabled' was changed for all questions
        $new_questionPropertiesToCheck = $this->getQuestionPropertiesToCheck($createdTest);
        $addedQuestionCount = Question::count() - $startQuestionsCount;
        $addedQuestionAddToDatabaseDisabledCount = $new_questionPropertiesToCheck->reduce(fn($carry, $questionProperties) => $carry + ($questionProperties['add_to_database_disabled'] === true ? 1 : 0));
        $this->assertEquals($addedQuestionCount, $addedQuestionAddToDatabaseDisabledCount);
        $addedQuestionOwnerIdCorrectCount = $new_questionPropertiesToCheck->reduce(fn($carry, $questionProperties) => $carry + ($questionProperties['owner_id'] === $fileManagement->schoolLocation->getKey() ? 1 : 0));
        $this->assertEquals($addedQuestionCount, $addedQuestionOwnerIdCorrectCount);
        $addedQuestionDerivedQuestionIdNotNullCount = $new_questionPropertiesToCheck->reduce(fn($carry, $questionProperties) => $carry + ($questionProperties['derived_question_id'] !== null ? 1 : 0));
        $this->assertEquals($addedQuestionCount, $addedQuestionDerivedQuestionIdNotNullCount);

        //assert Test_authors are removed and replaced by the new teacher
        $testAuthors = TestAuthor::where('test_id', '=', $createdTest->getKey())->get();
        $this->assertEquals(1, $testAuthors->count());
        $this->assertEquals($fileManagement->user_id, $testAuthors->first()->user_id);

        //assert Question_authors are removed and replaced by the new teacher
        $questionAuthorsForCreatedTest = QuestionAuthor::whereIn('question_id', $this->getAllQuestionsForTestQueryBuilder($createdTest))->get();

        $this->assertGreaterThan(0, $questionAuthorsForCreatedTest->count());
        $questionAuthorsForCreatedTest->each(function ($questionAuthor) use ($fileManagement) {
            $this->assertEquals($fileManagement->user_id, $questionAuthor->user_id);
        });
    }

    private function getQuestionPropertiesToCheck(Test $test)
    {
        return $test->refresh()->listOfTakeableTestQuestions()->map->question->reduce(function ($carry, $question) {
            $carry[$question->getKey()] = [
                'add_to_database_disabled' => $question->parentInstance->add_to_database_disabled,
                'owner_id'                 => $question->parentInstance->owner_id,
                'derived_question_id'      => $question->parentInstance->derived_question_id,
            ];

            if ($question->subQuestions) {
                $question->subQuestions->each(function ($subQuestion) use (&$carry) {
                    $carry[$subQuestion->getKey()] = [
                        'add_to_database_disabled' => $subQuestion->parentInstance->add_to_database_disabled,
                        'owner_id'                 => $subQuestion->parentInstance->owner_id,
                        'derived_question_id'      => $subQuestion->parentInstance->derived_question_id,
                    ];
                });
            }

            return $carry;
        }, collect());
    }

    private function getAllQuestionsForTestQueryBuilder(Test $test)
    {
        $testQuestionsQuery = TestQuestion::select('question_id as id')->whereTestId($test->getKey());
        $groupQuestionQuestionsQuery = GroupQuestionQuestion::select('question_id as id')->whereIn('group_question_id', $testQuestionsQuery);

        $allQuestionIdsQuery = $testQuestionsQuery->unionAll($groupQuestionQuestionsQuery);

        return $allQuestionIdsQuery;
    }

    private function createNewFileManagementAndTest()
    {
        $test_baker_username = "t.bakker@test-correct.nl";

        $requestingTeacherUser = User::find(1486);
        $toetsenbakkerUser = User::where('username', '=', $test_baker_username)->first();

        $fileManagement = new FileManagement([
            "id"                        => "d3a54346-4609-422a-9490-3290c000f448",
            "school_location_id"        => 1,
            "user_id"                   => $requestingTeacherUser->getKey(),
            "origname"                  => "New FileManagement",
            "name"                      => "New FileManagement",
            "type"                      => "testupload",
            "typedetails"               => [
                "name"                          => "New FileManagement",
                "education_level_year"          => 2,
                "contains_publisher_content"    => "0",
                "multiple"                      => 0,
                "form_id"                       => "d3a54346-4609-422a-9490-3290c000f448",
                "correctiemodel"                => 1,
                "subject_id"                    => 1,
                "education_level_id"            => 1,
                "test_kind_id"                  => 3,
                "colorcode"                     => null,
                "invite"                        => null,
                "test_upload_additional_option" => "0",
            ],
            "file_management_status_id" => 7,
            "handledby"                 => $toetsenbakkerUser->getKey(),
            "test_builder_code"         => "TEST",
            "notes"                     => "",
            "created_at"                => "2022-12-07T08:51:12.000000Z",
            "updated_at"                => "2022-12-07T08:52:19.000000Z",
            "deleted_at"                => null,
            "planned_at"                => "2023-01-06T23:00:00.000000Z",
            "parent_id"                 => null,
            "archived"                  => 0,
            "uuid"                      => "d3a54346-4609-422a-9490-3290c000f448",
            "form_id"                   => "d3a54346-4609-422a-9490-3290c000f448",
            "class"                     => null,
            "subject"                   => null,
            "subject_id"                => 1,
            "education_level_year"      => 2,
            "education_level_id"        => 1,
            "test_kind_id"              => 3,
            "test_name"                 => "New FileManagement",
            "orig_filenames"            => null,
        ]);


        //override test properties with period/subject from requestingTeacher because of implementation of creating the test in the application
        $period = PeriodRepository::getCurrentPeriodForSchoolLocation($fileManagement->schoolLocation);

        //create Test as toetsenbakker user
        $test = FactoryScenarioTestTestWithTwoQuestions::createTest('New FileManagement', $toetsenbakkerUser);

        $test->subject_id = $fileManagement->subject_id;
        $test->period_id = $period->getKey();
        $test->name = $fileManagement->test_name;
        $test->test_kind_id = $fileManagement->test_kind_id;
        $test->education_level_id = $fileManagement->education_level_id;
        $test->education_level_year = $fileManagement->education_level_year;

        auth()->login($toetsenbakkerUser);
        $test->save();

        $fileManagement->test_id = $test->getKey();

        return $fileManagement;
    }
}
