<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoice;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\TestQuestion;
use Tests\TestCase;

/**
 * FactoryQuestionMultipleChoiceTest
 *
 * Test Functionality of Multiple Choice Question Factory
 *
 *
 */
class FactoryQuestionMultipleChoiceTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_add_multiple_choice_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoice::create()
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountAnswers, MultipleChoiceQuestionAnswer::count());
    }

    /** @test */
    public function can_add_multiple_choice_question_with_correct_test_id()
    {
        $testFactory = FactoryTest::create();
        $testId = $testFactory->getTestId();

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoice::create()
        ]);

        $QuestionTestId = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['test_id'];

        $this->assertEquals($testId, $QuestionTestId);
    }

    /** @test */
    public function can_add_multiple_choice_question_with_correct_calculated_score()
    {
        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionMultipleChoice::create()
                    ->addAnswers([
                        [
                            "order" => 1,
                            "answer" => "answer false",
                            "score" => 0,
                        ],
                        [
                            "order" => 2,
                            "answer" => "answer correct score 4",
                            "score" => 4,
                        ],
                        [
                            "order" => 3,
                            "answer" => "answer correct score 6",
                            "score" => 6,
                        ],
                    ])
            ]);

        $expectedScore = 10;
        $questionPropertiesScore = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['score'];

        $this->assertEquals($expectedScore, $questionPropertiesScore);
    }

    /** @test */
    public function can_add_multiple_choice_question_with_correct_calculated_selectable_answers()
    {
        $customAnswers = [
            [
                "order" => 1,
                "answer" => "answer false",
                "score" => 0,
            ],
            [
                "order" => 2,
                "answer" => "answer correct score 4",
                "score" => 4,
            ],
            [
                "order" => 3,
                "answer" => "answer correct score 6",
                "score" => 6,
            ],
        ];
        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionMultipleChoice::create()
                    ->addAnswers($customAnswers),
            ]);

        $expectedSelectableAnswers = collect($customAnswers)->reduce(function($carry, $answer) {
            return ($answer['score'] > 0) ? $carry+1 : $carry;
        }, 0);

        $questionPropertiesSelectableAnswers = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['selectable_answers'];

        $this->assertEquals($expectedSelectableAnswers, $questionPropertiesSelectableAnswers);
    }

    /** @test */
    public function can_add_custom_answers_to_a_multiple_choice_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();
        $customAnswers = [
            0 => [
                "order" => 1,
                "answer" => "two (2)",
                "score" => 0,
            ],
            1 => [
                "order" => 2,
                "answer" => "one (1)",
                "score" => 4,
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoice::create()
                ->addAnswers($customAnswers),
        ]);

        $this->assertGreaterThan($startCountQuestions, TestQuestion::count());
        $this->assertGreaterThan($startCountAnswers, MultipleChoiceQuestionAnswer::count());

        $this->assertEquals(
            $customAnswers,
            $testFactory
                ->getPropertyByName('questions')[0]
                ->getPropertyByName('questionProperties')['answers']
        );
    }

    /** @test */
    public function can_add_custom_answers_and_description_to_a_multiple_choice_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $customQuestion = "<p>Which number is smaller than 2?</p>\n";
        $customAnswers = [
            [
                "order" => 1,
                "answer" => "two (2)",
                "score" => 0,
            ],
            [
                "order" => 2,
                "answer" => "one (1)",
                "score" => 4,
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoice::create()
                ->setProperties(['question' => $customQuestion])
                ->addAnswers($customAnswers),
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertEquals($startCountAnswers + 2, MultipleChoiceQuestionAnswer::count());

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
     * @dataProvider MultipleChoicePropertiesProvider
     * @test
     */
    public function can_add_custom_properties_to_a_multiple_choice_question(array $properties)
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoice::create()->setProperties($properties)
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountAnswers, MultipleChoiceQuestionAnswer::count());

        $testFactoryFirstQuestionProperties = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties');

        foreach ($properties as $key => $value) {
            $this->assertTrue($testFactoryFirstQuestionProperties[$key] === $value);
        }

    }

    public function MultipleChoicePropertiesProvider()
    {
        return [
            'question' => [
                [
                    'question' => '<p>Arbitrary new question</p>',
                ]
            ],
            'all_or_nothing' => [
                [
                    'all_or_nothing' => true,
                ]
            ],
            'is_open_source_content' => [
                [
                    'is_open_source_content' => 1,
                ]
            ],
        ];
    }
}
