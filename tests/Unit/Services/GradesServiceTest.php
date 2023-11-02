<?php

namespace Tests\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionCompletionCompletion;
use tcCore\Factories\Questions\FactoryQuestionCompletionMulti;
use tcCore\Factories\Questions\FactoryQuestionGroup;
use tcCore\Factories\Questions\FactoryQuestionInfoscreen;
use tcCore\Factories\Questions\FactoryQuestionMatchingClassify;
use tcCore\Factories\Questions\FactoryQuestionMatchingMatching;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoice;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceARQ;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceTrueFalse;
use tcCore\Factories\Questions\FactoryQuestionOpenLong;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Factories\Questions\FactoryQuestionOpenWriting;
use tcCore\Factories\Questions\FactoryQuestionRanking;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeRated;
use tcCore\Lib\Answer\AnswerChecker;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Question;
use tcCore\Services\GradesService;
use Tests\ScenarioLoader;
use Tests\TestCase;
use tcCore\TestTake;

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
        $studentRows = collect($gradeList->slice(1));

        $testQuestionCount = count($testTake->test->getQuestionOrderList());

        foreach ($studentRows as $studentRow) {
            $this->assertEquals(count($headerRow), count($studentRow), "The student row array has a different length than the header row array.");

            $this->assertTrue(Str::contains($studentRow['full_name'], ','), "The full name does not contain a comma separating the last name and the first name.");
            $this->assertTrue(is_numeric($studentRow['final_grade']), "The final grade is not numeric.");

            $questionGradeColumnsCount = collect($studentRow)->forget(['full_name', 'final_grade'])->count();
            $this->assertEquals($testQuestionCount, $questionGradeColumnsCount, "The number of question grade columns is not equal to the number of questions in the test.");
        }
    }

    /** @test */
    public function example()
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

        $gradeList = GradesService::getForTestTake($testTake);

        $headerRow = collect($gradeList->first());
        $studentRows = collect($gradeList->slice(1));

        $studentRows->each(function ($studentRow) use ($headerRow, $amountOfQuestionsAddedToTest) {
            $studentRow = collect($studentRow);
            //assert that also with tests that have carousel questions, the correct amount of values is returned for each student row
            $this->assertEquals(count($headerRow), $studentRow->count(), "The student row array has a different length than the header row array.");


            $studentRow->forget(['full_name', 'final_grade']);
            $this->assertEquals(
                $amountOfQuestionsAddedToTest, $studentRow->count(),
                sprintf("%s questions were added to the test and the student row is not equal to that.", $amountOfQuestionsAddedToTest)
            );

            if ($studentRow->first() === '-') {
                $this->assertTrue($studentRow->every(fn($value) => $value === '-'), "The student that did not join the test take does not have a - for every question.");
            }
            $this->assertTrue($studentRow->contains('X'), "The student row does not contain an X for a carousel question.");
        });
    }
}
