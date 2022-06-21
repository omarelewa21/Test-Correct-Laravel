<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\CompletionQuestion;
use tcCore\CompletionQuestionAnswer;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionCompletionMulti;
use tcCore\TestQuestion;
use Tests\TestCase;

/**
 * FactoryQuestionCompletionMultiTest
 *
 * Test Functionality of Completion Completion Question Factory
 */
class FactoryQuestionCompletionMultiTest extends TestCase
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
            FactoryQuestionCompletionMulti::create()
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
            FactoryQuestionCompletionMulti::create()
        ]);

        $QuestionTestId = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['test_id'];

        $this->assertEquals($testId, $QuestionTestId);
    }

    /** @test */
    public function can_set_a_question_with_included_answers_to_completion_question()
    {
        $questionWithAnswerText = '<p>an [apple|banana|pear] a day, keeps the [doctor|mechanic|programmer] away.</p>';
        $startCountCompletionAnswers = CompletionQuestionAnswer::count();

        $testFactory = FactoryTest::create()->addQuestions([
            FactoryQuestionCompletionMulti::create()
                ->setProperties(['question' => $questionWithAnswerText]),
        ]);

        $this->assertEquals(
            $startCountCompletionAnswers + 6, //six answer options, [apple|banana|pear] & [doctor|mechanic|programmer]
            CompletionQuestionAnswer::count()
        );
    }

    /** @test */
    public function can_set_score_of_completion_question()
    {
        $testFactory = FactoryTest::create()->addQuestions([
            FactoryQuestionCompletionMulti::create()->setScore(10),
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
            FactoryQuestionCompletionMulti::create()
                ->setProperties([
                    'question' => '<p>question answers: [correct|false|incorrect]</p>',
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
        $this->assertEquals('<p>question answers: [correct|false|incorrect]</p>', $propertiesBag['question']);
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
            FactoryQuestionCompletionMulti::create()->setProperties($properties)
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
