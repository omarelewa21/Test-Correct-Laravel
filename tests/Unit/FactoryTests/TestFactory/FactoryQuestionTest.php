<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\CompletionQuestion;
use tcCore\Factories\FactoryTest;
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
use tcCore\MatchingQuestion;
use tcCore\MultipleChoiceQuestion;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\OpenQuestion;
use tcCore\RankingQuestionAnswer;
use tcCore\TestQuestion;
use Tests\TestCase;

/**
 * FactoryQuestionTest:
 *
 * Test functionality of adding Questions to a test in general
 * Test adding each kind of Question and multiple questions at a time.
 *
 */
class FactoryQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;


    public function dump_list_yet_to_make_Questions()
    {
        $clearFormatting = "\e[0m";
        $ready = "\e[42;30m";
        $partialyReady = "\e[43;30m";
        $notReady = "\e[41;30m";

        $echolist = [
            $ready . "'InfoscreenQuestion'     => 'ready',",
            $ready . "'RankingQuestion'        => 'ready',",
            $ready . "'OpenQuestion'           => [",
            $ready . "    'short'         => 'ready',",
            $ready . "    'medium/long'   => 'ready',",
            $ready . "],",
            $notReady . "'DrawingQuestion'        => '',",
            $ready . "'MultipleChoiceQuestion' => [",
            $ready . "    'truefalse'      => 'ready',",
            $ready . "    'multiplechoice' => 'ready',",
            $ready . "    'arq'            => 'ready',",
            $ready . "],",
            $ready . "'CompletionQuestion'     => [",
            $ready . "    'multi'         => 'ready',",
            $ready . "    'completion'    => 'ready',",
            $ready . "],",
            $ready . "'MatchingQuestion'      => [",
            $ready . "    'matching'      => 'ready',",
            $ready . "    'classify'      => 'ready',",
            $ready . "]",
        ];
        dump('Question Factory completion list (without adding question to group):');
        echo $clearFormatting . "\n";
        foreach ($echolist as $line) {
            echo $line . "\n" . "$clearFormatting";
        }
        echo $partialyReady .
            "Status: Test can be made, with all/any questionType(s), except DrawingQuestion and Grouping of questions" .
            $clearFormatting;

        $this->assertTrue(true);
    }

    /** @test */
    public function can_add_a_random_question_to_a_test()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestion::count();
        $startCountOpenQuestions = OpenQuestion::count();

        $testFactory = FactoryTest::create()->addRandomQuestions(); //default === (int) 1

        //with random questions it is impossible to assert equals, or greater than with the specific types.
        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
    }

    /** @test */
    public function can_add_a_multitude_of_random_questions_to_a_test()
    {
        $amountOfQuestions = 5;
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestion::count();
        $startCountOpenQuestions = OpenQuestion::count();

        $testFactory = FactoryTest::create()->addRandomQuestions($amountOfQuestions);

        //with random questions it is impossible to assert equals, or greater than with the specific types.
        $this->assertEquals($startCountQuestions + $amountOfQuestions, TestQuestion::count());
    }


    /** @test */
    public function can_add_all_question_to_a_test()
    {
        $startCountQuestions = TestQuestion::count();

        $testFactory = FactoryTest::create()->addAllQuestions();

        $this->assertGreaterThan($startCountQuestions, TestQuestion::count());
    }

    /** @test */
    public function can_add_multiple_questions_to_a_test()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountMultipleChoiceAnswers = MultipleChoiceQuestionAnswer::count();
        $startCountRankingAnswers = RankingQuestionAnswer::count();
        $startCountOpenQuestions = OpenQuestion::count();
        $startCountMatchingQuestions = MatchingQuestion::count();
        $startCountCompletionQuestions = CompletionQuestion::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionInfoscreen::create(),
            FactoryQuestionRanking::create(),
            FactoryQuestionOpenShort::create(),
            FactoryQuestionOpenLong::create(),
            FactoryQuestionMultipleChoiceTrueFalse::create(),
            FactoryQuestionMultipleChoice::create(),
            FactoryQuestionMultipleChoiceARQ::create(),
            FactoryQuestionCompletionCompletion::create(),
            FactoryQuestionCompletionMulti::create(),
            FactoryQuestionMatchingMatching::create(),
            FactoryQuestionMatchingClassify::create(),
        ]);

        $this->assertEquals($startCountQuestions + 11, TestQuestion::count());
        $this->assertGreaterThan($startCountMultipleChoiceAnswers, MultipleChoiceQuestionAnswer::count());
        $this->assertGreaterThan($startCountRankingAnswers, RankingQuestionAnswer::count());
        $this->assertGreaterThan($startCountOpenQuestions, OpenQuestion::count());
        $this->assertGreaterThan($startCountMatchingQuestions, MatchingQuestion::count());
        $this->assertGreaterThan($startCountCompletionQuestions, CompletionQuestion::count());
    }
}
