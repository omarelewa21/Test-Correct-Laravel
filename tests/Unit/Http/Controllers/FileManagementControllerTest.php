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

    /** @test */
    public function testDuplicateTestToSchool()
    {
        //login as account manager user
        Auth::loginUsingId(520);

//        $fileManagement = FileManagement::find('8b956983-8c68-4450-b4a5-abee30988572');
//        $test = Test::find(238);

        $fileManagement = $this->createNewFileManagementAndTest();
        Auth::loginUsingId(520);

//        $test = $fileManagement->test; //$fileManagement doesnt have a Test relation yet in this branch
        $test = Test::find($fileManagement->test_id);

        $startTestCount = Test::count();
        $startTestQuestionsCount = TestQuestion::count();
        $startQuestionsCount = Question::count();

        $start_school_location = $test->owner_id; //2
        $start_author = $test->author_id; //1500
        $start_period = $test->period_id;
        $start_subject = $test->subject_id;

        //assert 'add_to_database_disabled' is false for all questions
        $start_statusses = $this->getAddToDatabaseDisabledStatussesForTest($test);
        $originalQuestionsAddToDatabaseDisabledTrueCount = $start_statusses->reduce(fn ($carry, $add_to_database_disabled) => $carry + ($add_to_database_disabled === true ? 1 : 0));
        $this->assertEquals(0, $originalQuestionsAddToDatabaseDisabledTrueCount);

        //request validates if auth user is an "Account Manager"
        (new FileManagementController())->duplicateTestToSchool(new DuplicateFileManagementTestRequest(), $fileManagement);

        $createdTest = Test::orderByDesc('created_at')->first();

        //assert properties of the original test are changed for the duplicate
        $this->assertNotEquals($start_author, $createdTest->author_id);
        $this->assertNotEquals($start_school_location, $createdTest->owner_id);
        $this->assertNotEquals($start_period, $createdTest->period_id);
        $this->assertNotEquals($start_subject, $createdTest->subject_id);

        //assert all parts were duplicated (and not existing questions linked to a new test)
        $this->assertGreaterThan($startTestCount, Test::count());
        $this->assertGreaterThan($startTestQuestionsCount, TestQuestion::count());
        $this->assertGreaterThan($startQuestionsCount, Question::count());

        //assert 'add_to_database_disabled' was changed for all questions
        $new_statusses = $this->getAddToDatabaseDisabledStatussesForTest($createdTest);
        $addedQuestionCount = Question::count() - $startQuestionsCount;
        $addedQuestionAddToDatabaseDisabledCount = $new_statusses->reduce(fn ($carry, $add_to_database_disabled) => $carry + ($add_to_database_disabled === true ? 1 : 0));
        $this->assertEquals($addedQuestionCount, $addedQuestionAddToDatabaseDisabledCount);

        //assert Test_authors are removed and replaced by the new teacher
        $testAuthors = TestAuthor::where('test_id', '=', $createdTest->getKey())->get();
        $this->assertEquals(1, $testAuthors->count());
        $this->assertEquals($fileManagement->user_id, $testAuthors->first()->user_id); //todo make this pass

        //assert Question_authors are removed and replaced by the new teacher
        $questionAuthorsForCreatedTest = QuestionAuthor::whereIn('question_id', $this->getAllQuestionsForTestQueryBuilder($createdTest))->get();

        $this->assertGreaterThan(0, $questionAuthorsForCreatedTest->count());
        $questionAuthorsForCreatedTest->each(function ($questionAuthor) use ($fileManagement) {
                $this->assertEquals($fileManagement->user_id, $questionAuthor->user_id);
        });
    }

    private function getAddToDatabaseDisabledStatussesForTest(Test $test)
    {
        return $test->refresh()->listOfTakeableTestQuestions()->map->question->reduce(function ($carry, $question) {
            $carry[$question->getKey()] = $question->parentInstance->add_to_database_disabled;

            if($question->subQuestions){
                $question->subQuestions->each(function ($subQuestion) use (&$carry) {
                    $carry[$subQuestion->getKey()] = $subQuestion->parentInstance->add_to_database_disabled;
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
        $teacher_user_id = 1486;
        $test_baker_user_id = 1500; //using RTTI school as other school

        $test = FactoryScenarioTestTestWithTwoQuestions::createTest('New FileManagement', User::find($test_baker_user_id));

        $fileManagement = new FileManagement([
            "id" => "d3a54346-4609-422a-9490-3290c000f448",
            "school_location_id" => 1,
            "user_id" => 1486,
            "origname" => "New FileManagement",
            "name" => "New FileManagement",
            "type" => "testupload",
            "typedetails" => [
                "name" => "New FileManagement",
                "education_level_year" => 2,
                "contains_publisher_content" => "0",
                "multiple" => 0,
                "form_id" => "d3a54346-4609-422a-9490-3290c000f448",
                "correctiemodel" => 1,
                "subject_id" => 1,
                "education_level_id" => 1,
                "test_kind_id" => 3,
                "colorcode" => null,
                "invite" => null,
                "test_upload_additional_option" => "0",
            ],
            "file_management_status_id" => 7,
            "handledby" => 1496,
            "test_builder_code" => "TEST",
            "notes" => "",
            "created_at" => "2022-12-07T08:51:12.000000Z",
            "updated_at" => "2022-12-07T08:52:19.000000Z",
            "deleted_at" => null,
            "planned_at" => "2023-01-06T23:00:00.000000Z",
            "parent_id" => null,
            "archived" => 0,
            "uuid" => "d3a54346-4609-422a-9490-3290c000f448",
            "form_id" => "d3a54346-4609-422a-9490-3290c000f448",
            "class" => null,
            "subject" => null,
            "subject_id" => 1,
            "education_level_year" => 2,
            "education_level_id" => 1,
            "test_kind_id" => 3,
            "test_name" => "New FileManagement",
            "orig_filenames" => null,

            //"test_id" => $test->getKey(), //todo add test_id
        ]);

        //todo FileManagement dont have test relations in this branch yet
        $fileManagement->test_id = $test->getKey();

        return $fileManagement;
    }
}
