<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionRanking;
use tcCore\RankingQuestionAnswer;
use tcCore\TestQuestion;
use Tests\TestCase;

/**
 * FactoryQuestionRankingTest
 *
 * Test Functionality of Ranking Question Factory
 *
 *
 */
class FactoryQuestionRankingTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_add_ranking_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = RankingQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionRanking::create()
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountAnswers, RankingQuestionAnswer::count());
    }

    /** @test */
    public function can_add_ranking_question_with_correct_test_id()
    {
        $testFactory = FactoryTest::create();
        $testId = $testFactory->getTestId();

        $testFactory->addQuestions([
            FactoryQuestionRanking::create()
        ]);

        $QuestionTestId = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['test_id'];

        $this->assertEquals($testId, $QuestionTestId);
    }
    /** @test */
    public function can_add_custom_score_to_ranking_question()
    {
        $custom_score = '10.0'; //score gets cast to int, float|string is no problem
        $expected_score = 10;

        $testFactory = FactoryTest::create()->addQuestions([
            FactoryQuestionRanking::create()->setScore($custom_score)
        ]);

        $QuestionScore = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['score'];

        $this->assertEquals($expected_score, $QuestionScore);
    }

    /** @test */
    public function can_add_custom_answers_to_a_ranking_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = RankingQuestionAnswer::count();
        $testFactory = FactoryTest::create();
        $customAnswers = [
            0 => [
                "order" => 1,
                "answer" => "one (1)",
            ],
            1 => [
                "order" => 2,
                "answer" => "one (2)",
            ],
            2 => [
                "order" => 2,
                "answer" => "three (3)",
            ],
            3 => [
                "order" => 2,
                "answer" => "four (4)",
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionRanking::create()
                ->addAnswers($customAnswers),
        ]);

        $this->assertGreaterThan($startCountQuestions, TestQuestion::count());
        $this->assertGreaterThan($startCountAnswers, RankingQuestionAnswer::count());

        $this->assertEquals(
            $customAnswers,
            $testFactory
                ->getPropertyByName('questions')[0]
                ->getPropertyByName('questionProperties')['answers']
        );
    }

    /** @test */
    public function can_add_custom_answers_and_description_to_a_ranking_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = RankingQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $customQuestion = "<p>Rank from high to low</p>\n";
        $customAnswers = [
            [
                "order" => 1,
                "answer" => "two (2)",
            ],
            [
                "order" => 2,
                "answer" => "one (1)",
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionRanking::create()
                ->setProperties(['question' => $customQuestion])
                ->addAnswers($customAnswers),
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertEquals($startCountAnswers + 2, RankingQuestionAnswer::count());

        $this->assertEquals(
            $customQuestion,
            $testFactory
                ->getPropertyByName('questions')[0]
                ->getPropertyByName('questionProperties')['question']
        );

        $this->assertEquals(
            $customAnswers,
            $testFactory
                ->getPropertyByName('questions')[0]
                ->getPropertyByName('questionProperties')['answers']
        );
    }

    /**
     * @dataProvider RankingPropertiesProvider
     * @test
     */
    public function can_add_custom_properties_to_a_ranking_question(array $properties)
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = RankingQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionRanking::create()->setProperties($properties)
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountAnswers, RankingQuestionAnswer::count());

        $testFactoryFirstQuestionProperties = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties');

        foreach ($properties as $key => $value) {
            $this->assertTrue($testFactoryFirstQuestionProperties[$key] === $value);
        }

    }

    public function RankingPropertiesProvider()
    {
        return [
            'question' => [
                [
                    'question' => '<p>Arbitrary new question</p>',
                ]
            ],
            'add_to_database' => [
                [
                    'add_to_database' => 0,
                ]
            ],
            'score' => [
                [
                    'score' => 10,
                ]
            ],
        ];
    }
}
