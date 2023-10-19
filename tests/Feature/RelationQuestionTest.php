<?php

namespace Feature;

use Livewire\Livewire;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryWord;
use tcCore\Factories\FactoryWordList;
use tcCore\Factories\Questions\FactoryQuestionRelation;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioTest;
use tcCore\Http\Enums\WordType;
use tcCore\Http\Livewire\Teacher\Cms\Constructor;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Relation;
use tcCore\RelationQuestion;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\User;
use tcCore\Word;
use Tests\ScenarioLoader;
use Tests\TestCase;

class RelationQuestionTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    protected User $teacherOne;
    protected Test $test;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacherOne = ScenarioLoader::get('teacher1');
        $this->test = FactoryTest::create(user: $this->teacherOne)->getTestModel();
    }

    /** @test */
    public function can_create_relation_question()
    {
        $this->assertDatabaseEmpty(RelationQuestion::class);
        $testFactory = FactoryTest::create($this->teacherOne);
        $testFactory->addQuestions([
            FactoryQuestionRelation::create()
        ]);
        $this->assertDatabaseCount(RelationQuestion::class, 1);
    }

    /*
         * Blue cells will be given as question to the students,
         * the first white cell is what they have to answer (from left to right).
         * So this is normally the language subject,
         *  or when the language subject is selected, the translation.
         * */

    /** @test */
    public function can_select_a_different_column_to_be_asked()
    {
        $test = FactoryTest::create($this->teacherOne)
            ->addQuestions([FactoryQuestionRelation::create()])
            ->getTestModel();
        $rela = RelationQuestion::whereIn(
            'id',
            TestQuestion::whereTestId($test->getKey())->select('question_id')
        )->first();

        $rela->wordsToAsk()->each(function ($word) {
            $this->assertEquals(WordType::SUBJECT, $word->type);
        });

        $rela->selectColumn(WordType::TRANSLATION);

        $rela->wordsToAsk()->each(function ($word) {
            $this->assertEquals(WordType::TRANSLATION, $word->type);
        });
    }

    /** @test */
    public function can_get_correct_answer_for_asked_word_when_it_is_the_subject_word()
    {
        $subjectWord = FactoryWord::create($this->teacherOne, ['text' => 'Fiets', 'type' => WordType::SUBJECT])->word;
        $translationWord = FactoryWord::create($this->teacherOne, ['text' => 'Bicycle', 'type' => WordType::TRANSLATION]
        )->linkToSubjectWord($subjectWord)
            ->word;

        $relationQuestion = $this->buildQuestionInTestWithWords($subjectWord);

        $word = $relationQuestion->wordsToAsk()->first();
        $this->assertEquals($word->text, $subjectWord->text);

        $answerWord = $relationQuestion->answerForWord($word);

        $this->assertEquals($answerWord->text, $translationWord->text);
    }

    /** @test */
    public function can_get_correct_answer_for_asked_word_if_it_is_a_different_type()
    {
        $subjectWord = FactoryWord::create($this->teacherOne, ['text' => 'Fiets', 'type' => WordType::SUBJECT])->word;
        $translationWord = FactoryWord::create($this->teacherOne, ['text' => 'Bicycle', 'type' => WordType::TRANSLATION]
        )
            ->linkToSubjectWord($subjectWord)
            ->word;
        $definition = FactoryWord::create($this->teacherOne, ['text' => 'trapding', 'type' => WordType::DEFINITION])
            ->linkToSubjectWord($subjectWord)
            ->word;

        $relationQuestion = $this->buildQuestionInTestWithWords($subjectWord);

        $relationQuestion->selectColumn(WordType::DEFINITION);
        $word = $relationQuestion->wordsToAsk()->first();
        $answerWord = $relationQuestion->answerForWord($word);
        $this->assertEquals($word->text, $definition->text);
        $this->assertEquals($answerWord->text, $subjectWord->text);

        $relationQuestion->selectColumn(WordType::TRANSLATION);
        $word = $relationQuestion->wordsToAsk()->first();
        $answerWord = $relationQuestion->answerForWord($word);
        $this->assertEquals($word->text, $translationWord->text);
        $this->assertEquals($answerWord->text, $subjectWord->text);
    }

    private function buildQuestionInTestWithWords(?Word $subjectWord): RelationQuestion
    {
        $wordList = FactoryWordList::create($this->teacherOne)
            ->addRow([$subjectWord])
            ->addRow()
            ->wordList;
        $test = FactoryTest::create($this->teacherOne)
            ->addQuestions([FactoryQuestionRelation::create()->useLists([$wordList])])
            ->getTestModel();
        return RelationQuestion::whereIn(
            'id',
            TestQuestion::whereTestId($test->getKey())->select('question_id')
        )->first();
    }
}