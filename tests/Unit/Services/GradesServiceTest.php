<?php

namespace Tests\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;
use Ramsey\Uuid\Uuid;
use tcCore\Exports\TestTakesExport;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryUser;
use tcCore\Factories\Questions\FactoryQuestionGroup;
use tcCore\Factories\Questions\FactoryQuestionInfoscreen;
use tcCore\Factories\Questions\FactoryQuestionMatchingClassify;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoice;
use tcCore\Factories\Questions\FactoryQuestionOpenLong;
use tcCore\Factories\Questions\FactoryQuestionOpenWriting;
use tcCore\Factories\Questions\FactoryQuestionRanking;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeRated;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Services\GradesService;
use tcCore\TestTakeStatus;
use Tests\ScenarioLoader;
use Tests\TestCase;

class GradesServiceTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    protected $teacherOne;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherOne = ScenarioLoader::get('teacher1');
    }

    /**
     * Can create a grade list for a test with all question types. including normal group questions
     * @test
     */
    public function canCreateGradeListForTestWithAllQuestions()
    {
        $testTake = FactoryScenarioTestTakeRated::createTestTake($this->teacherOne);

        $gradeList = GradesService::getForTestTake($testTake);

        $headerRow = collect($gradeList->first());
        $studentGradesRows = collect($gradeList->slice(1));

        $testQuestionCount = count($testTake->test->getQuestionOrderList());

        foreach ($studentGradesRows as $studentGradesRow) {
            $this->assertEquals(count($headerRow), count($studentGradesRow), "The student row array has a different length than the header row array.");

            $this->assertTrue(Str::contains($studentGradesRow['full_name'], ','), "The full name does not contain a comma separating the last name and the first name.");
            $this->assertTrue(is_numeric($studentGradesRow['final_grade']), "The final grade is not numeric.");

            $questionGradeColumnsCount = collect($studentGradesRow)->forget(['full_name', 'final_grade'])->count();
            $this->assertEquals($testQuestionCount, $questionGradeColumnsCount, "The number of question grade columns is not equal to the number of questions in the test.");
        }
    }

    /**
     * Can create a grade list when one of the test participants (student) did not make the test.
     *  students that did not make the test have hyphens for not answered questions and null for final_grade
     * @test
     */
    public function canCreateGradeListWithStudentsThatDidNotMakeTest()
    {
        $testTake = FactoryScenarioTestTakeRated::createTestTake($this->teacherOne);

        //add test_participant that has no answers and has status 'not taken'
        $studentThatDidNotTakeTest = FactoryUser::createStudent(schoolLocation: SchoolLocation::latest('created_at')->first());
        $testTake->testParticipants()->create([
            "user_id"             => $studentThatDidNotTakeTest->user->getKey(),
            "school_class_id"     => SchoolClass::latest('id')->first()->getKey(),
            "test_take_status_id" => TestTakeStatus::STATUS_TEST_NOT_TAKEN
        ]);

        $gradeList = GradesService::getForTestTake($testTake);

        foreach (collect($gradeList->slice(1)) as $studentGradesRow) {
            if(is_null($studentGradesRow['final_grade'])) {
                $questionGrades = collect($studentGradesRow)->forget(['full_name', 'final_grade']);
                $this->assertTrue(
                    condition: $questionGrades->every(fn($value) => $value === '-'),
                    message: "The student that did not join the test take does not have a - for every question."
                );
                continue;
            }

            $this->assertTrue(is_numeric($studentGradesRow['final_grade']), "The final grade is not numeric.");
        }
    }

    /**
     * Can create a grade list when one of the test participants (student) did not finish the test but started it.
     * eg. left questions unanswered.
     * These unanswered questions get 0,0 points.
     * @test
     */
    public function canCreateGradeListWithStudentThatAnsweredATestPartially()
    {
        $testTakeFactory = FactoryScenarioTestTakeRated::create($this->teacherOne)->testTakeFactory;
        $testTake = $testTakeFactory->testTake;

        //add test_participant that has no answers and has status 'not taken'
        $studentThatDidNotTakeTest = FactoryUser::createStudent(schoolLocation: SchoolLocation::latest('created_at')->first());
        $otherTestParticipant = $testTake->testParticipants->last();



        $newTestParticipant = $testTake->testParticipants()->create([
            "user_id"             => $studentThatDidNotTakeTest->user->getKey(),
            "school_class_id"     => SchoolClass::latest('id')->first()->getKey(),
            "test_take_status_id" => TestTakeStatus::STATUS_TEST_NOT_TAKEN
        ]);

        //replicate answers from other test_participant
        // test_participant only answered questions 1-5, the rest is unanswered
        $replicatedAnswers = collect();
        foreach ($otherTestParticipant->answers as $key => $answer) {
            $replicant = $answer->replicate();
            $replicant->test_participant_id = $newTestParticipant->getKey();
            $replicant->uuid = Uuid::uuid4();

            //test_participant only answered questions 1-5:
            if($key > 4) {
                $replicant->json = null;
                $replicant->done = 0;
                $replicant->final_rating = null;
            }

            $replicatedAnswers->push($replicant);
        }
        $newTestParticipant->answers()->saveMany($replicatedAnswers);

        //calculate final grade for new test_participant
        $testTakeFactory->testTake->refresh();
        $testTakeFactory->setNormalizedScores();


        $gradeList = GradesService::getForTestTake($testTake);

        $newTestParticipantGrades = $gradeList->last();

        $answeredQuestionGrades = collect($newTestParticipantGrades)->reverse()->slice(9)->reverse();
        $unansweredQuestionGrades = collect($newTestParticipantGrades)->slice(-9);

        $unansweredQuestionGrades->each(function ($grade) {
            $this->assertEquals("0,0", $grade, "The grade for an unanswered question is not 0,0");
        });

        $this->assertTrue(is_numeric($answeredQuestionGrades['final_grade']), "The final grade is not numeric.");
    }

    /**
     * Can create a grade list that also has carousel questions.
     * questions in a carousel question that are not given to a test_particpant should be marked with a 'X' instead of a hypen '-'
     * @test
     */
    public function canCreateGradesListWithCarouselQuestions()
    {
        $amountOfQuestionsAddedToTest = 9;

        $test = FactoryTest::create($this->teacherOne)
            ->setProperties(['name' => 'Test for gradelist. [with 9 questions] ' . Carbon::now()->format('ymd-Hi')])
            ->addQuestions( // question number in test:
                [
                    FactoryQuestionInfoscreen::create(), //question 1
                    FactoryQuestionRanking::create(), //question 2
                    FactoryQuestionGroup::create()
                        ->setProperties(
                            ['groupquestion_type' => 'carousel', 'number_of_subquestions' => 2, 'name' => 'Carousel group question']
                        )
                        ->addQuestions(
                            [
                                FactoryQuestionOpenLong::create() //question 3
                                ->setProperties(['question' => '<p>I am part of a group! Q1</p>']),
                                FactoryQuestionOpenLong::create() //question 4
                                ->setProperties(['question' => '<p>I am part of a group! Q2</p>']),
                                FactoryQuestionMultipleChoice::create() //question 5
                                ->setProperties(['question' => '<p>Multiple choice sub question! Q3</p>'])
                            ]
                        ),
                    FactoryQuestionMatchingClassify::create(), //question 6
                    FactoryQuestionGroup::create()
                        ->addQuestions(
                            [
                                FactoryQuestionOpenLong::create() //question 7
                                ->setProperties(['question' => '<p>I am part of a group! QQ1</p>']),
                                FactoryQuestionMultipleChoice::create() //question 8
                                ->setProperties(['question' => '<p>Multiple choice sub question!</p>'])
                            ]
                        ),
                    FactoryQuestionOpenWriting::create(), //question 9
                ]
            )
            ->getTestModel();


        $testTake = FactoryScenarioTestTakeRated::createTestTake($this->teacherOne, test: $test);

        $studentThatDidNotTakeTest = FactoryUser::createStudent(schoolLocation: SchoolLocation::latest('created_at')->first());

        $testTake->testParticipants()->create([
            "user_id"             => $studentThatDidNotTakeTest->user->getKey(),
            "school_class_id"     => SchoolClass::latest('id')->first()->getKey(),
            "test_take_status_id" => TestTakeStatus::STATUS_TEST_NOT_TAKEN
        ]);

        $gradeList = GradesService::getForTestTake($testTake);

        $headerRow = collect($gradeList->first());
        $studentGradesRows = collect($gradeList->slice(1));

        $studentGradesRows->each(function ($studentGradesRow) use ($headerRow, $amountOfQuestionsAddedToTest) {
            $studentGradesRow = collect($studentGradesRow);
            //assert that also with tests that have carousel questions, the correct amount of values is returned for each student row
            $this->assertEquals(count($headerRow), $studentGradesRow->count(), "The student row array has a different length than the header row array.");


            $studentGradesRow->forget(['full_name', 'final_grade']);
            $this->assertEquals(
                $amountOfQuestionsAddedToTest, $studentGradesRow->count(),
                sprintf("%s questions were added to the test and the student row is not equal to that.", $amountOfQuestionsAddedToTest)
            );

            if ($studentGradesRow->get(1) === '-') {
                $this->assertTrue($studentGradesRow->every(fn($value) => $value === '-'), "The student that did not join the test take does not have a - for every question.");
                return;
            }
            $this->assertTrue($studentGradesRow->contains('X'), "The student row does not contain an X for a carousel question.");
        });
    }
}
