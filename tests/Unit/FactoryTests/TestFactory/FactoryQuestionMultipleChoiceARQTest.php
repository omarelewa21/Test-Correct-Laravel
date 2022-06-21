<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceARQ;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\TestQuestion;
use Tests\TestCase;

/**
 * FactoryQuestionMultipleChoiceTest
 *
 * Test Functionality of Multiple Choice ARQ Question Factory
 * ARQ = Assertion Reason Question
 *
 */
class FactoryQuestionMultipleChoiceARQTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_add_an_ARQ_multiple_choice_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceARQ::create()
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountAnswers, MultipleChoiceQuestionAnswer::count());
    }

    /** @test */
    public function can_add_ARQ_multiple_choice_question_with_correct_test_id()
    {
        $testFactory = FactoryTest::create();
        $testId = $testFactory->getTestId();

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceARQ::create()
        ]);

        $QuestionTestId = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['test_id'];

        $this->assertEquals($testId, $QuestionTestId);
    }

    /** @test */
    public function can_add_an_ARQ_multiple_choice_question_with_correct_calculated_score()
    {
        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionMultipleChoiceARQ::create()
                    ->addAnswers([
                        0 => [
                            'answer' => '',
                            'score' => '0',
                        ],
                        1 => [
                            'answer' => '',
                            'score' => '4',
                        ],
                        2 => [
                            'answer' => '',
                            'score' => '0',
                        ],
                        3 => [
                            'answer' => '',
                            'score' => '6',
                        ],
                        4 => [
                            'answer' => '',
                            'score' => '0',
                        ],
                    ])
            ]);

        $expectedScore = 6;
        $questionPropertiesScore = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['score'];

        $this->assertEquals($expectedScore, $questionPropertiesScore);
    }

    /** @test */
    public function can_add_ARQ_multiple_choice_question_with_correct_calculated_selectable_answers()
    {
        $customAnswers = [
            0 => [
                'answer' => '',
                'score' => '0',
            ],
            1 => [
                'answer' => '',
                'score' => '0',
            ],
            2 => [
                'answer' => '',
                'score' => '8',
            ],
            3 => [
                'answer' => '',
                'score' => '0',
            ],
            4 => [
                'answer' => '',
                'score' => '0',
            ],
        ];
        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionMultipleChoiceARQ::create()
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
    public function can_add_custom_answers_to_an_ARQ_multiple_choice_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();
        $customAnswers = [
            0 => [
                'answer' => '',
                'score' => '5', //Assertion is correct, Reason too, and assertion is correct because of the reason
            ],
            1 => [
                'answer' => '',
                'score' => '0',
            ],
            2 => [
                'answer' => '',
                'score' => '0',
            ],
            3 => [
                'answer' => '',
                'score' => '0',
            ],
            4 => [
                'answer' => '',
                'score' => '0',
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceARQ::create()
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
    public function can_add_custom_answers_and_description_to_an_ARQ_multiple_choice_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $customQuestion = "<p>Assertion: The Earth is flat</p>
                            <p>Reason: Pidgeons are drones belonging to the government</p>";
        $customAnswers = [
            0 => [
                'answer' => '',
                'score' => '0',
            ],
            1 => [
                'answer' => '',
                'score' => '5',
            ],
            2 => [
                'answer' => '',
                'score' => '0',
            ],
            3 => [
                'answer' => '',
                'score' => '0',
            ],
            4 => [
                'answer' => '',
                'score' => '5', //both the assertion as the reason are false
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceARQ::create()
                ->setProperties(['question' => $customQuestion])
                ->addAnswers($customAnswers),
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertEquals($startCountAnswers + 5, MultipleChoiceQuestionAnswer::count());

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
    public function can_add_custom_properties_to_an_ARQ_multiple_choice_question(array $properties)
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceARQ::create()->setProperties($properties)
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
                    'question' => '<p>Arbitrary new question</p><p>assertion: </p><p>reason: </p>',
                ]
            ],
            'add_to_database' => [
                [
                    'add_to_database' => 0,
                ]
            ],
        ];
    }
}
