<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryWord;
use tcCore\Factories\FactoryWordList;
use tcCore\Factories\Questions\FactoryQuestionRelation;
use tcCore\RelationQuestion;
use tcCore\User;
use tcCore\WordList;
use Tests\ScenarioLoader;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionRanking;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
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
class FactoryQuestionRelationTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    private User $teacherOne;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacherOne = ScenarioLoader::get('teachers')->first();
    }

    /** @test */
    public function can_create_relation_question()
    {
        $testFactory = FactoryTest::create($this->teacherOne);
        $this->assertDatabaseEmpty(RelationQuestion::class);
        $testFactory->addQuestions([
            FactoryQuestionRelation::create()
        ]);
        $this->assertDatabaseCount(RelationQuestion::class, 1);
    }

    /** @test */
    public function can_create_relation_question_with_default_words()
    {
        $testFactory = FactoryTest::create($this->teacherOne);

        $testFactory->addQuestions([
            FactoryQuestionRelation::create()
        ]);

        $this->assertNotEmpty(RelationQuestion::first()->words);
    }


    /** @test */
    public function can_link_words_to_relation_question()
    {
        $testFactory = FactoryTest::create($this->teacherOne);
        $wordList = FactoryWordList::create($this->teacherOne)
            ->addRow()
            ->addRow()
            ->wordList;

        $testFactory->addQuestions([
            FactoryQuestionRelation::create()->useLists([$wordList])
        ]);

        $this->assertTrue(RelationQuestion::first()->wordLists->contains($wordList));
        foreach (RelationQuestion::first()->words as $word) {
            $this->assertTrue($wordList->words->contains($word));
        }
    }
}
