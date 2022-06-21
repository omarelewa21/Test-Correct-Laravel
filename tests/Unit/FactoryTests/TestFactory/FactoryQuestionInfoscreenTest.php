<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionInfoscreen;
use tcCore\Question;
use tcCore\TestQuestion;
use Tests\TestCase;

/**
 * FactoryQuestionInfoscreenTest
 *
 * Test Functionality of Infoscreen Question Factory
 *
 * Infoscreen only accepts: question, maintain_position, closable
 * (also attachments)
 */
class FactoryQuestionInfoscreenTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_add_infoscreen_question()
    {
        $startCountTestQuestions = TestQuestion::count();
        $startCountQuestions = Question::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionInfoscreen::create()
        ]);

        $this->assertEquals($startCountQuestions + 1, Question::count());
        $this->assertEquals($startCountTestQuestions + 1, TestQuestion::count());
    }

    /** @test */
    public function can_add_ranking_question_with_correct_test_id()
    {
        $testFactory = FactoryTest::create();
        $testId = $testFactory->getTestId();

        $testFactory->addQuestions([
            FactoryQuestionInfoscreen::create()
        ]);

        $QuestionTestId = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['test_id'];

        $this->assertEquals($testId, $QuestionTestId);
    }

    /**
     * @dataProvider InfoscreenPropertiesProvider
     * @test
     */
    public function can_add_custom_properties_to_a_Infoscreen_question(array $properties)
    {
        $startCountQuestions = TestQuestion::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionInfoscreen::create()->setProperties($properties)
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());

        $testFactoryFirstQuestionProperties = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties');

        foreach ($properties as $key => $value) {
            $this->assertTrue($testFactoryFirstQuestionProperties[$key] === $value);
        }

    }

    public function InfoscreenPropertiesProvider()
    {
        return [
            'question' => [
                [
                    'question' => '<p>This is a information screen</p>',
                ]
            ],
            'dont shuffle' => [
                [
                    'maintain_position' => '1',
                ]
            ],
            'closeable/sluiten na beantwoorden' => [
                [
                    'closeable' => 1,
                ]
            ],
        ];
    }
}
