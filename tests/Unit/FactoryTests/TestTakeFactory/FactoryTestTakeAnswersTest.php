<?php

namespace Tests\Unit\FactoryTests\TestTakeFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Answer;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryTestTake;
use tcCore\Factories\Questions\FactoryQuestionCompletionCompletion;
use tcCore\Factories\Questions\FactoryQuestionCompletionMulti;
use tcCore\Factories\Questions\FactoryQuestionInfoscreen;
use tcCore\Factories\Questions\FactoryQuestionMatchingClassify;
use tcCore\Factories\Questions\FactoryQuestionMatchingMatching;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoice;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceARQ;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceTrueFalse;
use tcCore\Factories\Questions\FactoryQuestionOpenLong;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Factories\Questions\FactoryQuestionRanking;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithAllQuestionTypes;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithOpenShortQuestion;
use Tests\TestCase;
use const Tests\Unit\TestTakeFactory\STATUS_TAKEN;


class FactoryTestTakeAnswersTest extends TestCase
{
    const STATUS_PLANNED = 1;
    const STATUS_TEST_NOT_TAKEN = 2;
    const STATUS_TAKING_TEST = 3;
    const STATUS_HANDED_IN = 4;
    const STATUS_TAKEN_AWAY = 5;
    const STATUS_TAKEN = 6;
    const STATUS_DISCUSSING = 7;
    const STATUS_DISCUSSED = 8;
    const STATUS_RATED = 9;


    use DatabaseTransactions;
    use WithFaker;


    /** @test */
    public function can_add_participants_with_answers_to_testTake_status_taking_test()
    {
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()//testParticipants enter test -> status to taking test.
            ->fillTestParticipantsAnswers();//set answers for all questions, for all participants

        $firstParticipant = $factoryTestTake->testTake->testParticipants->first();
        $firstAnswer = $firstParticipant->answers->first();

        $this->assertEquals(1, $firstAnswer->done);
        $this->assertGreaterThan(0, $firstAnswer->time);
        $this->assertNotNull($firstAnswer->json);
    }


    /** @test */
    public function can_create_taken_test_take_with_answers()
    {
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $test->name = 'takenTestWithAnswers';
        $test->save();

        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()//testParticipants enter test -> status to taking test.
            ->fillTestParticipantsAnswers()
            ->setStatusTaken();

        $firstParticipant = $factoryTestTake->testTake->testParticipants->first();
        $firstAnswer = $firstParticipant->answers->first();

        $this->assertEquals(1, $firstAnswer->done);
        $this->assertGreaterThan(0, $firstAnswer->time);
        $this->assertNotNull($firstAnswer->json);
        $this->assertEquals(self::STATUS_TAKEN, $factoryTestTake->testTake->testTakeStatus->id);
        $this->assertEquals(self::STATUS_TAKEN, $firstParticipant->testTakeStatus->id);
    }

    public function provideQuestions()
    {
        return [
            'completion question' => [
                [
                    FactoryQuestionCompletionCompletion::create(),
                    FactoryQuestionCompletionMulti::create(),
                ],
            ],
            'ranking question' => [
                [
                    FactoryQuestionRanking::create(),
                ],
            ],
            'open question' => [
                [
                    FactoryQuestionOpenShort::create(),
                    FactoryQuestionOpenLong::create(),
                ],
            ],
            'infoscreen question' => [
                [
                    FactoryQuestionInfoscreen::create(),
                ],
            ],
            'multiplechoice question' => [
                [
                    FactoryQuestionMultipleChoice::create(),
                    FactoryQuestionMultipleChoiceARQ::create(),
                    FactoryQuestionMultipleChoiceTrueFalse::create(),
                ],
            ],
            'matching question' => [
                [
                    FactoryQuestionMatchingMatching::create(),
                    FactoryQuestionMatchingClassify::create(),
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideQuestions
     * @test
     */
    public function can_add_test_with_answers_for_various_questions($questions)
    {
        $test = FactoryTest::create()->addQuestions($questions)->getTestModel();

        $startCountAnswers = Answer::count();

        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()
            ->fillTestParticipantsAnswers();

        $this->assertGreaterThan(
            $startCountAnswers,
            Answer::count()
        );
    }

    /** @test */
    public function can_add_answers_for_testScenario_with_all_question_types_to_testTake()
    {
        $test = FactoryScenarioTestTestWithAllQuestionTypes::createTest();
        $startCountAnswers = Answer::count();

        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()
            ->fillTestParticipantsAnswers();

        $this->assertGreaterThan($startCountAnswers, Answer::count());
    }
}