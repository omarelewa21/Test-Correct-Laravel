<?php

namespace Tests\Unit;

use Illuminate\Validation\UnauthorizedException;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionRelation;
use tcCore\FactoryScenarios\FactoryScenarioSchoolWordLists;
use tcCore\Http\Enums\WordType;
use tcCore\Lib\Models\VersionManager;
use tcCore\Word;
use tcCore\WordList;
use Tests\TestCase;
use Tests\Traits\VersionableTestTrait;
use ValueError;

class WordTest extends TestCase
{
    use VersionableTestTrait;

    protected $loadScenario = FactoryScenarioSchoolWordLists::class;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpVersionableTest();
    }

    /** @test */
    public function cannot_create_a_new_word_with_a_non_enum_type_value()
    {
        $this->expectException(ValueError::class);

        Word::make([
            'word'                 => 'Kaas',
            'type'                 => 'alternative',
            'subject_id'           => 1,
            'education_level_id'   => 1,
            'education_level_year' => 1,
            'school_location_id'   => 1,
        ])
            ->associateAuthor($this->teacherOne)
            ->save();
    }

    /** @test */
    public function can_add_word_to_a_word_list()
    {
        $wordList = $this->defaultWordList();

        $wordList->createWord('Kaas', WordType::SUBJECT);

        $this->assertNotEmpty($wordList->words);
    }

    /** @test */
    public function can_get_word_lists_that_make_use_of_a_word()
    {
        $word = $this->defaultWord();
        $wordList1 = $this->defaultWordList(name: 'testlist1', user: $this->teacherTwo);
        $wordList2 = $this->defaultWordList(name: 'testlist2', user: $this->teacherThree);

        $wordList1->words()->attach($word);
        $wordList2->words()->attach($word);

        $this->assertEquals(2, $word->wordLists->count());
        $this->assertEquals($this->teacherTwo->getKey(), $word->wordLists->first()->user_id);
        $this->assertEquals($this->teacherThree->getKey(), $word->wordLists->last()->user_id);
    }

    /** @test */
    public function can_edit_word_within_my_list()
    {
        $wordList = $this->defaultWordList();

        $wordList->createWord('Kaas', WordType::DEFINITION);
        $word = $wordList->words()->first();

        $this->assertEquals('Kaas', $word->text);
        VersionManager::getVersionable($wordList, $this->teacherOne)
            ->editWord($word, ['text' => 'Gele Gestolde Melk']);

        $word = $wordList->words()->first();
        $this->assertEquals('Gele Gestolde Melk', $word->text);
        $this->assertEquals(1, $word->versions()->count());
    }

    /** @test */
    public function can_see_original_word_within_list_when_teacher_two_changes_the_word_within_my_list()
    {
        // results to WordList with id = 1
        $wordList = $this->defaultWordList();
        $wordList->createWord('Kaas', WordType::DEFINITION);
        $word = $wordList->words()->first();

        // action triggers duplication of wordList and word pivot table new wordList id = 2 with origin 1
        // some extras;
        VersionManager::getVersionable($wordList, $this->teacherTwo)
            ->editWord($word, ['text' => 'Gele Gestolde Melk']);

        $count = VersionManager::getVersionable($wordList, $this->teacherOne)
            ->words()
            ->get()
            ->filter(fn($word) => $word->text == 'Kaas')
            ->count();

        $this->assertEquals(1, $count);

        $count2 = VersionManager::getVersionable($wordList, $this->teacherTwo)
            ->words()
            ->get()
            ->filter(fn($word) => $word->text == 'Kaas')
            ->count();

        $this->assertEquals(0, $count2);

        $this->assertEquals(2, $word->versions()->count());
        $this->assertEquals(2, $wordList->versions()->count());
    }

    /** @test */
    public function when_adding_a_new_word_to_an_existing_list_it_creates_a_new_version()
    {
        $wordList = $this->defaultWordList();

        VersionManager::getVersionable($wordList, $this->teacherThree)
            ->createWord('woordje', WordType::SUBJECT);

        $this->assertEquals(
            $wordList->getKey(),
            VersionManager::getVersionable(
                WordList::forUser($this->teacherOne)->first(),
                $this->teacherOne
            )->getKey()
        );
        $this->assertNotEquals(
            $wordList->getKey(),
            VersionManager::getVersionable(
                WordList::forUser($this->teacherThree)->first(),
                $this->teacherThree
            )->getKey()
        );
        $this->assertEquals(
            $this->teacherThree->id,
            VersionManager::getVersionable(
                $wordList,
                $this->teacherThree
            )->words()->first()->user_id
        );

        $this->assertEquals(
            2,
            $wordList->versions()->count()
        );
    }

    /** @test */
    public function can_add_existing_word_from_another_author_to_my_list()
    {
        $wordList1 = $this->defaultWordList();
        $wordList1->createWord('Bicyclette', WordType::SUBJECT);
        $word1 = $wordList1->words->first();

        $wordList2 = $this->defaultWordList('Vervoersmiddelen', $this->teacherTwo);
        $wordList2->addWord($word1);

        $this->assertEquals($wordList1->user_id, $this->teacherOne->getKey());
        $this->assertEquals($wordList2->user_id, $this->teacherTwo->getKey());

        $this->assertEquals(
            $wordList2->words()->first()->user_id,
            $this->teacherOne->getKey()
        );
    }

    /** @test */
    public function can_remove_word_from_my_list()
    {
        $wordList1 = $this->defaultWordList();
        $wordList1->createWord('Bicyclette', WordType::SUBJECT);
        $word = $wordList1->words()->first();

        $this->assertEquals(1, $wordList1->words()->count());

        VersionManager::getVersionable($wordList1, $this->teacherOne)->removeWord($word);

        $this->assertEquals(0, $wordList1->words()->count());
    }

    /** @test */
    public function when_removing_a_word_from_someone_elses_list_a_duplicate_is_created()
    {
        $wordList1 = $this->defaultWordList();
        $wordList1->createWord('Bicyclette', WordType::SUBJECT);
        $word = $wordList1->words()->first();

        $this->assertEquals(1, $wordList1->words()->count());

        VersionManager::getVersionable($wordList1, $this->teacherTwo)->removeWord($word);

        $this->assertEquals(1, $wordList1->words()->count());

        $this->assertEquals(
            0,
            VersionManager::getVersionable($wordList1, $this->teacherTwo)->words()->count()
        );

    }

    /** @test */
    public function when_removing_a_word_and_it_is_unused_it_will_be_deleted()
    {
        $wordList1 = $this->defaultWordList();
        $wordList1->createWord('Bicyclette', WordType::SUBJECT);
        $word = $wordList1->words()->first();

        $this->assertEquals(1, $wordList1->words()->count());
        VersionManager::getVersionable($wordList1, $this->teacherOne)->removeWord($word);
        $this->assertEquals(0, $wordList1->words()->count());

        $this->assertSoftDeleted($word);
    }

    /** @test */
    public function when_removing_a_word_and_it_is_used_it_will_not_be_deleted()
    {
        $wordList1 = $this->defaultWordList();
        $wordList1->createWord('Bicyclette', WordType::SUBJECT);
        $word = $wordList1->words()->first();

        VersionManager::getVersionable($wordList1, $this->teacherTwo)->addWord($word);
        FactoryTest::create($this->teacherOne)
            ->addQuestions([
                FactoryQuestionRelation::create()->useLists([
                    VersionManager::getVersionable($wordList1, $this->teacherTwo)
                ])
            ]);
        ;
        VersionManager::getVersionable($wordList1, $this->teacherOne)->removeWord($word);

        $this->assertNotSoftDeleted($word);
    }

    /** @test */
    public function can_get_all_associated_words_for_a_subject_word()
    {
        $wordList1 = $this->defaultWordList();
        $wordList1->createWord('Fiets', WordType::SUBJECT);
        $wordList1->createWord('Auto', WordType::SUBJECT);

        $mainWord = $wordList1->words()->where('text', 'Fiets')->first();

        $wordList1->createWord('Bicyclette', WordType::TRANSLATION, $mainWord);
        $wordList1->createWord('Vervoermiddel dat wordt aangedreven door trappen', WordType::DEFINITION, $mainWord);
        $wordList1->createWord('Tweewieler', WordType::SYNONYM, $mainWord);

        $this->assertEquals(3, $mainWord->associations->count());

        $mainWord->associations->each(function ($type) use ($mainWord) {
            $this->assertEquals($type->subjectWord->text, $mainWord->text);
        });
    }
}
