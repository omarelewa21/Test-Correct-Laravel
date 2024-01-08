<?php

namespace Tests\Unit\Http\Commands;

use tcCore\Answer;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryWordList;
use tcCore\Factories\Questions\FactoryQuestionGroup;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoice;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Factories\Questions\FactoryQuestionRelation;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTaken;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTakingTest;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Livewire\StudentPlayer\Question\RelationQuestion as RelationQuestionPlayerComponent;
use tcCore\Question;
use tcCore\RelationQuestion;
use tcCore\RelationQuestionWord;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\TestTakeRelationQuestion;
use tcCore\TestTakeStatus;
use tcCore\User;
use tcCore\Word;
use tcCore\WordList;
use tcCore\WordListWord;
use Tests\ScenarioLoader;
use Tests\TestCase;

class PurgeRelationQuestionCommandTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    private Test $test;
    private User $teacherOne;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherOne = ScenarioLoader::get('teacher1');
        $testFactory = FactoryTest::create($this->teacherOne, ['name' => 'duplitoets toets1']);
        $wordList = FactoryWordList::create($this->teacherOne)->addRows(20, 3)->wordList;
        $this->test = $testFactory->addQuestions([
            FactoryQuestionRelation::create()->useLists([$wordList]),
            FactoryQuestionMultipleChoice::create()
        ])
            ->getTestModel();
    }

    /** @test */
    public function can_purge_answers()
    {
        $testTake = FactoryScenarioTestTakeTaken::createTestTake(user: $this->teacherOne, test: $this->test);

        $testTakeAnswerCount = Answer::whereIn('test_participant_id', $testTake->testParticipants()->select('id'))->count();
        $answerCount = Answer::count();

        $this->artisan('purge:relation-question');

        $this->assertLessThan($answerCount, Answer::count());
        $this->assertLessThan($testTakeAnswerCount, Answer::whereIn('test_participant_id', $testTake->testParticipants()->select('id'))->count());
        $this->assertNotEquals(0, Answer::whereIn('test_participant_id', $testTake->testParticipants()->select('id'))->count());
    }

    /** @test */
    public function can_purge_test_questions()
    {
        $countBuilder = TestQuestion::join('questions', 'questions.id', '=', 'test_questions.question_id')->where('questions.type', 'RelationQuestion');

        $testQuestionsCount = $this->test->testQuestions()->count();
        $count = $countBuilder->count();

        $this->artisan('purge:relation-question');

        $this->assertLessThan($count, $countBuilder->count());
        $this->assertLessThan($testQuestionsCount, $this->test->testQuestions()->count());
        $this->assertNotEquals(0, TestQuestion::count());
    }

    /** @test */
    public function can_purge_group_question_questions()
    {
        $testFactory = FactoryTest::create($this->teacherOne, ['name' => 'duplitoets toets1']);
        $wordList = FactoryWordList::create($this->teacherOne)->addRows(20, 3)->wordList;
        $test = $testFactory->addQuestions([
            FactoryQuestionGroup::create()
                ->addQuestions([
                    FactoryQuestionRelation::create()->useLists([$wordList]),
                    FactoryQuestionOpenShort::create(),
                ]),
            FactoryQuestionMultipleChoice::create()
        ])
            ->getTestModel();


        $countBuilder = GroupQuestionQuestion::join('questions', 'questions.id', '=', 'group_question_questions.question_id')
            ->join('test_questions', 'test_questions.question_id', '=', 'group_question_questions.group_question_id')
            ->where('test_questions.test_id', $test->getKey())
            ->where('questions.type', 'RelationQuestion');

        $count = $countBuilder->count();
        $testQuestionCount = $test->getQuestionCount();
        $this->artisan('purge:relation-question');

        $this->assertLessThan($count, $countBuilder->count());
        $this->assertNotEquals(0, GroupQuestionQuestion::count());
        $this->assertEquals($testQuestionCount - 1, $test->getQuestionCount());
    }

    /** @test */
    public function can_purge_relation_question_words()
    {
        $this->assertNotEquals(0, RelationQuestionWord::count());

        $this->artisan('purge:relation-question');

        $this->assertDatabaseEmpty(RelationQuestionWord::class);
    }

    /** @test */
    public function can_purge_test_take_relation_questions()
    {
        $testFactory = FactoryTest::create($this->teacherOne, ['name' => 'duplitoets toets2']);
        $test = $testFactory->addQuestions([
            FactoryQuestionRelation::create()->setProperties(['shuffle' => true]),
        ])->getTestModel();
        $testTake = FactoryScenarioTestTakeTakingTest::createTestTake($this->teacherOne, test: $test);
        $testParticipant = $testTake->testParticipants()->first();
        $testParticipant->setAttribute('test_take_status_id', TestTakeStatus::STATUS_TAKING_TEST);
        $testParticipant->save();

        $this->actingAs($testParticipant->user);

        $this->get(route('student.test-take-laravel', $testTake->uuid))
            ->assertSeeLivewire(RelationQuestionPlayerComponent::class);

        $this->assertNotEquals(0, TestTakeRelationQuestion::count());

        $this->artisan('purge:relation-question');

        $this->assertDatabaseEmpty(TestTakeRelationQuestion::class);
    }

    /** @test */
    public function can_purge_word_list_words()
    {
        $this->assertNotEquals(0, WordListWord::count());

        $this->artisan('purge:relation-question');

        $this->assertDatabaseEmpty(WordListWord::class);
    }

    /** @test */
    public function can_purge_word_lists()
    {
        $this->assertNotEquals(0, WordList::count());

        $this->artisan('purge:relation-question');

        $this->assertDatabaseEmpty(WordList::class);
    }

    /** @test */
    public function can_purge_words()
    {
        $this->assertNotEquals(0, Word::count());

        $this->artisan('purge:relation-question');

        $this->assertDatabaseEmpty(Word::class);
    }

    /** @test */
    public function can_purge_relation_questions()
    {
        $this->assertNotEquals(0, RelationQuestion::count());
        $this->assertNotEquals(0, Question::where('type', 'RelationQuestion')->count());

        $this->artisan('purge:relation-question');

        $this->assertDatabaseEmpty(RelationQuestion::class);
        $this->assertEquals(0, Question::where('type', 'RelationQuestion')->count());
    }
}
