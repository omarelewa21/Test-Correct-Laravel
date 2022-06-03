<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\CompletionQuestion;
use tcCore\CompletionQuestionAnswer;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionCompletionCompletion;
use tcCore\TestQuestion;
use Tests\TestCase;

/**
 * FactoryQuestionCompletionCompletionTest
 *
 * Test Functionality of Completion Completion Question Factory
 */
class FactoryQuestionCompletionCompletionTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_add_completion_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountCompletionQuestions = CompletionQuestion::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionCompletionCompletion::create()
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountCompletionQuestions, CompletionQuestion::count());
    }

    /** @test */
    public function can_add_completion_question_with_correct_test_id()
    {
        $testFactory = FactoryTest::create();
        $testId = $testFactory->getTestId();

        $testFactory->addQuestions([
            FactoryQuestionCompletionCompletion::create()
        ]);

        $QuestionTestId = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['test_id'];

        $this->assertEquals($testId, $QuestionTestId);
    }

    /** @test */
    public function can_set_a_question_with_included_answers_to_completion_question()
    {
        $questionWithAnswerText = '<p>an [apple] a day, keeps the [doctor] away.</p>';
        $startCountCompletionAnswers = CompletionQuestionAnswer::count();

        $testFactory = FactoryTest::create()->addQuestions([
            FactoryQuestionCompletionCompletion::create()
                ->setProperties(['question' => $questionWithAnswerText]),
        ]);

        $this->assertEquals(
            $startCountCompletionAnswers + 2, //two answers, [apple] & [doctor]
            CompletionQuestionAnswer::count()
        );
    }

    /** @test */
    public function can_set_score_of_completion_question()
    {
        $testFactory = FactoryTest::create()->addQuestions([
            FactoryQuestionCompletionCompletion::create()->setScore(10),
        ]);

        $QuestionScore = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['score'];

        $this->assertEquals(10, $QuestionScore);
    }

    /** @test */
    public function can_chain_options_on_completion_question_factory_create()
    {
        $testFactory = FactoryTest::create()->addQuestions([
            FactoryQuestionCompletionCompletion::create()
                ->setProperties([
                    'question' => '<p>question [answer]</p>',
                ])
                ->setScore(10)
                ->setProperties([
                    'note_type' => 'TEXT',
                ]),
        ]);

        $propertiesBag = $testFactory->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties');

        $this->assertEquals(10, $propertiesBag['score']);
        $this->assertEquals('TEXT', $propertiesBag['note_type']);
        $this->assertEquals('<p>question [answer]</p>', $propertiesBag['question']);
    }


    /**
     * @dataProvider CompletionQuestionPropertiesProvider
     * @test
     */
    public function can_add_custom_properties_to_a_completion_question(array $properties)
    {
        $startCountQuestions = TestQuestion::count();
        $startCountCompletion = CompletionQuestion::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionCompletionCompletion::create()->setProperties($properties)
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountCompletion, CompletionQuestion::count());

        $testFactoryFirstQuestionProperties = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties');

        foreach ($properties as $key => $value) {
            $this->assertTrue($testFactoryFirstQuestionProperties[$key] === $value);
        }

    }

    public function CompletionQuestionPropertiesProvider()
    {
        return [
            'auto_check_answer' => [
                [
                    'auto_check_answer' => true,
                ]
            ],
            'auto_check_answer_case_sensitive' => [
                [
                    'auto_check_answer' => true,
                    'auto_check_answer_case_sensitive' =>  true,
                ]
            ],
            'add_to_database' => [
                [
                    'add_to_database' => 0,
                ]
            ],
            'note_type' => [
                [
                    'note_type' => 'TEXT',
                ]
            ],
            'discuss' => [
                [
                    'discuss' => false,
                ]
            ],
            'decimal_score' => [
                [
                    'decimal_score' => "1",
                ]
            ],
            'maintain_position' => [
                [
                    'maintain_position' => "1",
                ]
            ],
            'closeable' => [
                [
                    'closeable' => "1",
                ]
            ],
        ];
    }
}
