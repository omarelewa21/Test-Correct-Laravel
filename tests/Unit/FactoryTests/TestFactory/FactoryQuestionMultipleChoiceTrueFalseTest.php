<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceTrueFalse;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\TestQuestion;
use Tests\TestCase;

/**
 * FactoryQuestionMultipleChoiceTest
 *
 * Test Functionality of Multiple Choice True/False Question Factory
 *
 *
 */
class FactoryQuestionMultipleChoiceTrueFalseTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_add_True_False_multiple_choice_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceTrueFalse::create()
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountAnswers, MultipleChoiceQuestionAnswer::count());
    }

    /** @test */
    public function can_add_True_False_multiple_choice_question_with_correct_test_id()
    {
        $testFactory = FactoryTest::create();
        $testId = $testFactory->getTestId();

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceTrueFalse::create()
        ]);

        $QuestionTestId = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['test_id'];

        $this->assertEquals($testId, $QuestionTestId);
    }

    /** @test */
    public function can_add_True_False_multiple_choice_question_with_correct_calculated_score()
    {
        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionMultipleChoiceTrueFalse::create()
                    ->addAnswers([
                        [
                            'order' => 1,
                            'answer' => 'Juist',
                            'score' => 10,
                        ],
                        [
                            'order' => 2,
                            'answer' => 'Onjuist',
                            'score' => 0,
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
    public function can_add_True_False_multiple_choice_question_with_correct_calculated_selectable_answers()
    {
        $customAnswers = [
            [
                'order' => 1,
                'answer' => 'Juist',
                'score' => 0,
            ],
            [
                'order' => 2,
                'answer' => 'Onjuist',
                'score' => 5,
            ],
        ];
        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionMultipleChoiceTrueFalse::create()
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
            [
                'order' => 1,
                'answer' => 'Juist',
                'score' => 7,
            ],
            [
                'order' => 2,
                'answer' => 'Onjuist',
                'score' => 0,
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceTrueFalse::create()
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

        $customQuestion = "<p>Is a ball round?</p>\n";
        $customAnswers = [
            [
                'order' => 1,
                'answer' => 'Juist',
                'score' => 7,
            ],
            [
                'order' => 2,
                'answer' => 'Onjuist',
                'score' => 0,
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceTrueFalse::create()
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
    public function can_add_custom_properties_to_a_True_False_multiple_choice_question(array $properties)
    {
        $startCountQuestions = TestQuestion::count();
        $startCountAnswers = MultipleChoiceQuestionAnswer::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionMultipleChoiceTrueFalse::create()->setProperties($properties)
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
            'add_to_database' => [
                [
                    'add_to_database' => 0,
                ]
            ],
        ];
    }
}
