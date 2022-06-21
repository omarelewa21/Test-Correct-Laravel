<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionMatchingClassify;
use tcCore\MatchingQuestion;
use tcCore\TestQuestion;
use Tests\TestCase;

/**
 * FactoryQuestionMatchingClassifyTest
 *
 * Test Functionality of Matching Question Factory
 *
 */
class FactoryQuestionMatchingClassifyTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_add_classify_matching_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountMatchingQuestions = MatchingQuestion::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionMatchingClassify::create()
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountMatchingQuestions, MatchingQuestion::count());
    }

    /** @test */
    public function can_add_classify_matching_question_with_correct_test_id()
    {
        $testFactory = FactoryTest::create();
        $testId = $testFactory->getTestId();

        $testFactory->addQuestions([
            FactoryQuestionMatchingClassify::create()
        ]);

        $QuestionTestId = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['test_id'];

        $this->assertEquals($testId, $QuestionTestId);
    }

    /** @test */
    public function can_set_score_of_classify_matching_question()
    {
        $testFactory = FactoryTest::create()->addQuestions([
            FactoryQuestionMatchingClassify::create()->setScore(10),
        ]);

        $QuestionScore = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties')['score'];

        $this->assertEquals(10, $QuestionScore);
    }

    /** @test */
    public function can_chain_options_on_classify_matching_question_factory_create()
    {
        $testFactory = FactoryTest::create()->addQuestions([
            FactoryQuestionMatchingClassify::create()
                ->setProperties([
                    'question' => '<p>question</p>',
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
        $this->assertEquals('<p>question</p>', $propertiesBag['question']);
    }

    /** @test */
    public function can_add_custom_answers_to_a_classify_matching_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountMatching = MatchingQuestion::count();
        $testFactory = FactoryTest::create();
        $customAnswers = [
            [
                'order' => 1,
                'left' => 'Color',
                'right' => 'Red',
            ],
            [
                'order' => 2,
                'left' => 'Number',
                'right' => 'Two',
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionMatchingClassify::create()
                ->addAnswers($customAnswers),
        ]);

        $this->assertGreaterThan($startCountQuestions, TestQuestion::count());
        $this->assertGreaterThan($startCountMatching, MatchingQuestion::count());

        $this->assertEquals(
            $customAnswers,
            $testFactory
                ->getPropertyByName('questions')[0]
                ->getPropertyByName('questionProperties')['answers']
        );
    }

    /** @test */
    public function can_add_custom_classify_answers_to_a_classify_matching_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountMatching = MatchingQuestion::count();
        $testFactory = FactoryTest::create();
        $customAnswers = [
            [
                'order' => 1,
                'left' => 'Color',
                'right' => 'Red',
            ],
            [
                'order' => 2,
                'left' => 'Number',
                'right' => "Two\none\nfour", //double quotes required! linebreaks also!
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionMatchingClassify::create()
                ->addAnswers($customAnswers),
        ]);

        $matchingQuestionFactory = $testFactory->getPropertyByName('questions')[0];

        $array = collect($matchingQuestionFactory->lastTestQuestion->question->matchingQuestionAnswers->toArray())
            ->map(function ($matchingQuestion) {
                return [$matchingQuestion['type'] => $matchingQuestion['answer']];
            })->toArray();

        //assert that the count of all LEFT and RIGHT answers is 6.
        // color (left), red, number (left), two, one, four
        $this->assertEquals(6, count($array));

        $this->assertGreaterThan($startCountQuestions, TestQuestion::count());
        $this->assertGreaterThan($startCountMatching, MatchingQuestion::count());

        $this->assertEquals(
            $customAnswers,
            $testFactory
                ->getPropertyByName('questions')[0]
                ->getPropertyByName('questionProperties')['answers']
        );
    }

    /** @test */
    public function can_add_custom_answers_and_description_to_a_classify_matching_question()
    {
        $startCountQuestions = TestQuestion::count();
        $startCountMatchingQuestions = MatchingQuestion::count();
        $testFactory = FactoryTest::create();

        $customQuestion = "<p>Match the correct words</p>\n";
        $customAnswers = [
            [
                'order' => 1,
                'left' => 'Color',
                'right' => 'Red',
            ],
            [
                'order' => 2,
                'left' => 'Number',
                'right' => 'Two',
            ],
        ];

        $testFactory->addQuestions([
            FactoryQuestionMatchingClassify::create()
                ->setProperties(['question' => $customQuestion])
                ->addAnswers($customAnswers),
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertEquals($startCountMatchingQuestions + 1, MatchingQuestion::count());

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
     * @dataProvider MatchingQuestionPropertiesProvider
     * @test
     */
    public function can_add_custom_properties_to_a_classify_matching_question(array $properties)
    {
        $startCountQuestions = TestQuestion::count();
        $startCountMatchingQuestions = MatchingQuestion::count();
        $testFactory = FactoryTest::create();

        $testFactory->addQuestions([
            FactoryQuestionMatchingClassify::create()->setProperties($properties)
        ]);

        $this->assertEquals($startCountQuestions + 1, TestQuestion::count());
        $this->assertGreaterThan($startCountMatchingQuestions, MatchingQuestion::count());

        $testFactoryFirstQuestionProperties = $testFactory
            ->getPropertyByName('questions')[0]
            ->getPropertyByName('questionProperties');

        foreach ($properties as $key => $value) {
            $this->assertTrue($testFactoryFirstQuestionProperties[$key] === $value);
        }

    }

    public function MatchingQuestionPropertiesProvider()
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
