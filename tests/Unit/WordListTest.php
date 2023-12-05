<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Unit;

use InvalidArgumentException;
use Livewire\Livewire;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryWordList;
use tcCore\Factories\Questions\FactoryQuestionRelation;
use tcCore\FactoryScenarios\FactoryScenarioSchoolWordLists;
use tcCore\FactoryScenarios\FactoryScenarioTestTakePlanned;
use tcCore\Http\Enums\WordType;
use tcCore\Http\Livewire\Teacher\Cms\Constructor;
use tcCore\Http\Livewire\Teacher\Cms\WordListChangesModal;
use tcCore\Lib\Models\VersionManager;
use tcCore\RelationQuestion;
use tcCore\Word;
use tcCore\WordList;
use Tests\TestCase;
use Tests\Traits\VersionableTestTrait;

class WordListTest extends TestCase
{
    use VersionableTestTrait;

    protected $loadScenario = FactoryScenarioSchoolWordLists::class;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpVersionableTest();
    }

    /** @test */
    public function when_i_create_a_word_list_it_is_created_with_a_version()
    {
        $wordList = $this->defaultWordList();
        $this->assertEquals($wordList->versions()->first()->name, 1);
        $this->assertEquals($this->teacherOne, $wordList->user);
    }

    /** @test */
    public function when_i_create_a_word_list_and_teacherTwo_changes_the_name_there_are_two_versions()
    {
        $wordList = $this->defaultWordList();

        $countWordListsInDB = WordList::count();

        VersionManager::getVersionable($wordList, $this->teacherTwo)->edit(['name' => 'test2']);

        $this->assertEquals($countWordListsInDB + 1, WordList::count());

        $this->assertEquals($wordList->versions()->count(), 2);

        // als ik me ben dan zou ik versie 1 terug moeten krijgen;
        /* Gets original because its his list */
        $this->assertEquals(
            'testlist',
            VersionManager::getVersionable(WordList::forUser($this->teacherOne)->first(), $this->teacherOne)->name
        );
        /*Gets existing version of the wordlist because he edited it*/
        $this->assertEquals(
            'test2',
            VersionManager::getVersionable(WordList::forUser($this->teacherTwo)->first(), $this->teacherTwo)->name
        );
        /* Gets the latest non-original version of the wordlist because thats the most recent version */
        $this->assertEquals(
            'test2',
            VersionManager::getVersionable(WordList::forUser($this->teacherThree)->first(), $this->teacherThree)->name
        );
    }

    /** @test */
    public function can_remove_my_own_word_lists()
    {
        // TODO: figure out the rules for deleting (un)used wordlists
        $wordList1 = $this->defaultWordList();
        $this->assertNotEmpty(WordList::forUser($this->teacherOne)->get());

        $wordList1->remove();

        $this->assertSoftDeleted($wordList1);
        $this->assertEmpty(WordList::forUser($this->teacherOne)->get());
    }

    /** @test */
    public function can_always_get_my_version_even_when_giving_a_derived__list()
    {
        $teacherOneList = $this->defaultWordList();
        VersionManager::getVersionable($teacherOneList, $this->teacherTwo)->edit(['name' => 'mijn lijst']);
        $teacherTwoList = VersionManager::getVersionable($teacherOneList, $this->teacherTwo);

        $this->assertEquals(
            $teacherOneList->getKey(),
            VersionManager::getVersionable($teacherTwoList, $this->teacherOne)->getKey()
        );
    }

    /** @test */
    public function can_filter_existing_lists_on_subject()
    {
        $this->defaultWordList(user: $this->teacherOne, subjectId: 1);
        $this->defaultWordList(user: $this->teacherOne, subjectId: 3);
        $this->defaultWordList(user: $this->teacherTwo, subjectId: 2);
        $this->defaultWordList(user: $this->teacherThree, subjectId: 1);

        $this->assertEquals(
            2,
            WordList::filtered(['subject_id' => 1])->count()
        );
        $this->assertEquals(
            3,
            WordList::filtered(['subject_id' => [2, 1]])->count()
        );
        $this->assertEquals(
            1,
            WordList::filtered(['subject_id' => 3])->count()
        );
    }

    /** @test */
    public function can_filter_existing_lists_on_users()
    {
        $this->defaultWordList(user: $this->teacherOne);
        $this->defaultWordList(user: $this->teacherOne);
        $this->defaultWordList(user: $this->teacherTwo);
        $list = $this->defaultWordList(user: $this->teacherThree);
        VersionManager::getVersionable($list, $this->teacherOne)->edit(['name' => 'updatetje']);
        VersionManager::getVersionable($list, $this->teacherTwo)->edit(['name' => 'updatetje 2']);
        /*
        List counts
        teacherOne: 2 original 1 edit
        teacherTwo: 1 original 1 edit
        teacherThree: 1 original 0 edit
        */

        $this->assertEquals(
            3,
            WordList::filtered(['user_id' => $this->teacherOne->getKey()])->count()
        );
        $this->assertEquals(
            2,
            WordList::filtered(['user_id' => [$this->teacherTwo->getKey()]])->count()
        );
        $this->assertEquals(
            1,
            WordList::filtered(['user_id' => $this->teacherThree->getKey()])->count()
        );

        $this->assertEquals(
            3,
            WordList::filtered(['user_id' => [$this->teacherTwo->getKey(), $this->teacherThree->getKey()]])->count()
        );

        $this->assertEquals(
            4,
            WordList::filtered(['user_id' => [$this->teacherThree->getKey(), $this->teacherOne->getKey()]])->count()
        );
    }


    /** @test */
    public function can_have_a_word_count_and_a_row_count()
    {
        $wordList1 = $this->defaultWordList();
        $wordList1->createWord('Fiets', WordType::SUBJECT);
        $wordList1->createWord('Auto', WordType::SUBJECT);
        $mainWord = $wordList1->words()->where('text', 'Fiets')->first();
        $wordList1->createWord('Bicyclette', WordType::TRANSLATION, $mainWord);
        $wordList1->createWord('Vervoermiddel dat wordt aangedreven door trappen', WordType::DEFINITION, $mainWord);
        $wordList1->createWord('Tweewieler', WordType::SYNONYM, $mainWord);

        $this->assertEquals(2, $wordList1->rows()->count());
        $this->assertEquals(5, $wordList1->words()->count());
    }

    /** @test */
    public function can_create_a_row_of_words_in_a_list()
    {
        $wordList1 = $this->defaultWordList();
        $wordList1->createRow(
            ['text' => 'Vervoermiddel dat wordt aangedreven door trappen', 'type' => WordType::DEFINITION],
            ['text' => 'Fiets', 'type' => WordType::SUBJECT],
            ['text' => 'Bicyclette', 'type' => WordType::TRANSLATION],
            ['text' => 'Tweewieler', 'type' => WordType::SYNONYM],
        );

        $this->assertEquals(1, $wordList1->rows()->count());
        $this->assertEquals(4, $wordList1->words()->count());
    }

    /** @test */
    public function cannot_create_a_row_of_words_without_subject_language_word()
    {
        $this->expectException(InvalidArgumentException::class);
        $wordList1 = $this->defaultWordList();
        $wordList1->createRow(
            ['text' => 'Vervoermiddel dat wordt aangedreven door trappen', 'type' => WordType::DEFINITION],
            ['text' => 'Bicyclette', 'type' => WordType::TRANSLATION],
            ['text' => 'Tweewieler', 'type' => WordType::SYNONYM],
        );
    }

    /** @test */
    public function cannot_create_a_row_with_duplicate_types()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create a row with 2 words of the same type.');

        $wordList1 = $this->defaultWordList();
        $wordList1->createRow(
            ['text' => 'Fiets', 'type' => WordType::SUBJECT],
            ['text' => 'Bicyclette', 'type' => WordType::TRANSLATION],
            ['text' => 'Bicyclette2', 'type' => WordType::TRANSLATION],
        );
    }

    /** @test */
    public function can_add_a_row_of_existing_words()
    {
        $wordList1 = $this->defaultWordList();
        $subjectWord = $this->defaultWord('Fiets', WordType::SUBJECT);
        collect([
            ['Vervoermiddel dat wordt aangedreven door trappen', WordType::DEFINITION],
            ['Bicyclette', WordType::TRANSLATION],
            ['Tweewieler', WordType::SYNONYM],
        ])->each(function ($word) use ($subjectWord) {
            $this->defaultWord(
                $word[0],
                $word[1],
                subjectWord: $subjectWord
            );
        });

        $this->assertEmpty($wordList1->words()->get());

        $wordList1->addRow($subjectWord);

        $this->assertEquals(1, $wordList1->rows(true)->count());
        $this->assertEquals(4, $wordList1->words()->count());
    }

    /** @test */
    public function cannot_add_a_row_of_existing_words_when_not_inserting_the_subject_word()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('To add an existing row, insert the subject word.');

        $wordList1 = $this->defaultWordList();
        $subjectWord = $this->defaultWord('Fiets', WordType::SUBJECT);

        $word = $this->defaultWord(
            'Vervoermiddel dat wordt aangedreven door trappen',
            WordType::DEFINITION,
            subjectWord: $subjectWord
        );

        collect([['Bicyclette', WordType::TRANSLATION], ['Tweewieler', WordType::SYNONYM]])
            ->each(function ($word) use ($subjectWord) {
                $this->defaultWord(
                    $word[0],
                    $word[1],
                    subjectWord: $subjectWord
                );
            });

        $this->assertEmpty($wordList1->words()->get());

        $wordList1->addRow($word);

        $this->assertEquals(0, $wordList1->rows()->count());
        $this->assertEquals(0, $wordList1->words()->count());
    }


    /** @test */
    public function can_add_an_unused_row_of_existing_words_to_someone_elses_list_but_it_creates_a_new_version()
    {
        $wordList1 = $this->defaultWordList(user: $this->teacherOne);

        $subjectWord = $this->defaultWord('Fiets', WordType::SUBJECT, user: $this->teacherTwo);
        $word1 = $this->defaultWord(
            'Vervoermiddel dat wordt aangedreven door trappen',
            WordType::DEFINITION,
            user       : $this->teacherTwo,
            subjectWord: $subjectWord
        );
        $word2 = $this->defaultWord(
            'Bicyclette',
            WordType::TRANSLATION,
            user       : $this->teacherTwo,
            subjectWord: $subjectWord
        );
        $word3 = $this->defaultWord(
            'Tweewieler',
            WordType::SYNONYM,
            user       : $this->teacherTwo,
            subjectWord: $subjectWord
        );

        $this->assertEmpty($wordList1->words()->get());

        VersionManager::getVersionable($wordList1, $this->teacherTwo)->addRow($subjectWord);

        $this->assertEquals(0, VersionManager::getVersionable($wordList1, $this->teacherOne)->rows()->count());
        $this->assertEquals(0, VersionManager::getVersionable($wordList1, $this->teacherOne)->words()->count());

        $this->assertEquals(1, VersionManager::getVersionable($wordList1, $this->teacherTwo)->rows()->count());
        $this->assertEquals(4, VersionManager::getVersionable($wordList1, $this->teacherTwo)->words()->count());
    }

    /** @test */
    public function can_change_word_when_it_is_used_in_a_question_but_the_entire_list_will_be_duplicated()
    {
        $testFactory = FactoryTest::create($this->teacherOne);
        $wordList = FactoryWordList::create($this->teacherOne)->addRows()->wordList;
        $testFactory->addQuestions([FactoryQuestionRelation::create()->useLists([$wordList])]);

        $word1 = VersionManager::getVersionable($wordList, $this->teacherOne)->words()->first();
        $editedWord = VersionManager::getVersionable($wordList, $this->teacherOne)
            ->editWord($word1, ['text' => 'nootjes']);

        $this->assertNotEquals(
            RelationQuestion::first()->words()->first()->getKey(),
            $editedWord->getKey()
        );
        $this->assertEquals(
            RelationQuestion::first()->words()->first()->getKey(),
            $word1->getKey()
        );
    }

    /** @test */
    public function can_change_wordlist_when_it_is_used_in_a_question_but_it_will_be_duplicated()
    {
        $testFactory = FactoryTest::create($this->teacherOne);
        $wordList = FactoryWordList::create($this->teacherOne)->addRows()->wordList;

        $testFactory->addQuestions([FactoryQuestionRelation::create()->useLists([$wordList])]);

        VersionManager::getVersionable($wordList, $this->teacherOne)
            ->edit(['name' => 'nootjes lijst']);

        $this->assertNotEquals(
            RelationQuestion::first()->wordLists()->first()->getKey(),
            VersionManager::getVersionable($wordList, $this->teacherOne)->getKey()
        );
    }

    /** @test */
    public function can_get_a_diff_on_the_list_when_a_word_has_been_modified()
    {
        $word = $this->defaultWord(text: 'T1 word');
        $wordList = $this->defaultWordList();
        $wordList->addWord($word);

        $this->assertFalse(VersionManager::getVersionable($wordList, $this->teacherOne)->hasNewVersion());

        VersionManager::getVersionable($wordList, $this->teacherTwo)->editWord($word, ['text' => 'T2 word']);

        $this->assertTrue(VersionManager::getVersionable($wordList, $this->teacherOne)->hasNewVersion());

        $this->assertEquals(
            1,
            VersionManager::getVersionable($wordList, $this->teacherOne)
                ->getDiff()
                ->get('updated')
                ->count()
        );
    }

    /** @test */
    public function can_get_a_diff_on_the_list_when_a_word_has_been_added()
    {
        $word = $this->defaultWord(text: 'T1 word');
        $word2 = $this->defaultWord(text: 'T2 word');
        $wordList = $this->defaultWordList();
        $wordList->addWord($word);

        $this->assertFalse(VersionManager::getVersionable($wordList, $this->teacherOne)->hasNewVersion());

        VersionManager::getVersionable($wordList, $this->teacherTwo)->addWord($word2);

        $this->assertTrue(VersionManager::getVersionable($wordList, $this->teacherOne)->hasNewVersion());

        $this->assertEquals(
            1,
            VersionManager::getVersionable($wordList, $this->teacherOne)
                ->getDiff()
                ->get('created')
                ->count()
        );
    }

    /** @test */
    public function can_get_a_diff_on_the_list_when_a_word_has_been_removed()
    {
        $word = $this->defaultWord(text: 'T1 word');
        $word2 = $this->defaultWord(text: 'T2 word');
        $wordList = $this->defaultWordList();
        $wordList->addWord($word);
        $wordList->addWord($word2);

        $this->assertFalse(VersionManager::getVersionable($wordList, $this->teacherOne)->hasNewVersion());

        VersionManager::getVersionable($wordList, $this->teacherTwo)->removeWord($word);

        $this->assertTrue(VersionManager::getVersionable($wordList, $this->teacherOne)->hasNewVersion());

        $this->assertEquals(
            1,
            VersionManager::getVersionable($wordList, $this->teacherOne)
                ->getDiff()
                ->get('deleted')
                ->count()
        );
    }

    /** @test */
    public function can_get_a_diff_on_the_list_when_a_word_has_been_added_and_removed()
    {
        $wordToDelete = $this->defaultWord(text: 'T1 word');
        $word2 = $this->defaultWord(text: 'T1 word 2');
        $wordToAdd = $this->defaultWord(text: 'T2 word', user: $this->teacherTwo);
        $wordList = $this->defaultWordList();
        $wordList->addWord($wordToDelete);
        $wordList->addWord($word2);

        VersionManager::getVersionable($wordList, $this->teacherTwo)->addWord($wordToAdd);
        VersionManager::getVersionable($wordList, $this->teacherTwo)->removeWord($wordToDelete);

        $diff = VersionManager::getVersionable($wordList, $this->teacherOne)->getDiff();

        $this->assertEquals(1, $diff->get('deleted')->count());
        $this->assertEquals($wordToDelete->getKey(), $diff->get('deleted')->first()->getKey());

        $this->assertEquals(1, $diff->get('created')->count());
        $this->assertEquals($wordToAdd->getKey(), $diff->get('created')->first()->getKey());

        $this->assertEquals(0, $diff->get('updated')->count());

        $this->assertEquals(2, VersionManager::getVersionable($wordList, $this->teacherOne)->words()->count());
        $this->assertEquals(2, VersionManager::getVersionable($wordList, $this->teacherTwo)->words()->count());
    }

    /** @test */
    public function can_get_a_diff_on_the_list_when_a_word_has_been_added_and_removed_and_modified()
    {
        $wordToDelete = $this->defaultWord(text: 'T1 word');
        $wordToEdit = $this->defaultWord(text: 'T1 word 2');
        $wordToAdd = $this->defaultWord(text: 'T2 word', user: $this->teacherTwo);
        $wordList = $this->defaultWordList();
        $wordList->addWord($wordToDelete);
        $wordList->addWord($wordToEdit);

        VersionManager::getVersionable($wordList, $this->teacherTwo)->addWord($wordToAdd);
        VersionManager::getVersionable($wordList, $this->teacherTwo)->removeWord($wordToDelete);
        $editedWord = VersionManager::getVersionable($wordList, $this->teacherTwo)->editWord(
            $wordToEdit,
            ['text' => 'Verbetering!']
        );

        $diff = VersionManager::getVersionable($wordList, $this->teacherOne)->getDiff();

        $this->assertEquals(1, $diff->get('deleted')->count());
        $this->assertEquals($wordToDelete->getKey(), $diff->get('deleted')->first()->getKey());

        $this->assertEquals(1, $diff->get('created')->count());
        $this->assertEquals($wordToAdd->getKey(), $diff->get('created')->first()->getKey());

        $this->assertEquals(1, $diff->get('updated')->count());
        $this->assertEquals($editedWord->getKey(), $diff->get('updated')->first()->getKey());

        $this->assertEquals(2, VersionManager::getVersionable($wordList, $this->teacherOne)->words()->count());
        $this->assertEquals(2, VersionManager::getVersionable($wordList, $this->teacherTwo)->words()->count());
    }

    /** @test */
    public function can_get_a_diff_on_the_list_when_a_word_has_been_added_and_removed_and_modified_multiple_times()
    {
        $wordToDelete = $this->defaultWord(text: 'T1 word');
        $wordToEdit = $this->defaultWord(text: 'T1 word 2');
        $wordToAdd = $this->defaultWord(text: 'T2 word', user: $this->teacherTwo);
        $wordToAdd2 = $this->defaultWord(text: 'T2 word 2', user: $this->teacherTwo);

        $wordList = $this->defaultWordList();
        $wordList->addWord($wordToDelete);
        $wordList->addWord($wordToEdit);

        VersionManager::getVersionable($wordList, $this->teacherTwo)->addWord($wordToAdd);
        VersionManager::getVersionable($wordList, $this->teacherTwo)->addWord($wordToAdd2);
        VersionManager::getVersionable($wordList, $this->teacherTwo)->removeWord($wordToDelete);
        $editedWord = VersionManager::getVersionable($wordList, $this->teacherTwo)->editWord(
            $wordToEdit,
            ['text' => 'Verbetering!']
        );
        $editedWord2 = VersionManager::getVersionable($wordList, $this->teacherTwo)->editWord(
            $wordToAdd2,
            ['text' => 'Verbetering 2!']
        );

        $diff = VersionManager::getVersionable($wordList, $this->teacherOne)->getDiff();

        $this->assertEquals(1, $diff->get('deleted')->count());
        $this->assertEquals($wordToDelete->getKey(), $diff->get('deleted')->first()->getKey());

        $this->assertEquals(2, $diff->get('created')->count());
        $this->assertEquals($wordToAdd->getKey(), $diff->get('created')->first()->getKey());
        $this->assertEquals($wordToAdd2->getKey(), $diff->get('created')->last()->getKey());
        $this->assertEquals($editedWord2->getKey(), $wordToAdd2->getKey());

        $this->assertEquals(1, $diff->get('updated')->count());
        $this->assertEquals($editedWord->getKey(), $diff->get('updated')->first()->getKey());

        $this->assertEquals(2, VersionManager::getVersionable($wordList, $this->teacherOne)->words()->count());
        $this->assertEquals(3, VersionManager::getVersionable($wordList, $this->teacherTwo)->words()->count());
    }

    /** @test */
    public function can_()
    {
        $testFactory = FactoryTest::create($this->teacherOne);
        $wordList = FactoryWordList::create($this->teacherOne)->addRows()->wordList;
        $word = $wordList->words->first();

        $testFactory->addQuestions([FactoryQuestionRelation::create()->useLists([$wordList])])->getTestModel();
        $relationQuestion = RelationQuestion::first();

        VersionManager::getVersionable($wordList, $this->teacherTwo)->editWord($word, ['text' => 'T2 word']);

        $this->assertTrue(VersionManager::getVersionable($wordList, $this->teacherOne)->hasNewVersion());

        VersionManager::getVersionable($wordList, $this->teacherOne)->updateToLatestVersion($relationQuestion);
    }

//    /** @test */
//    public function can_1()
//    {
//        $testFactory = FactoryTest::create($this->teacherOne);
//        $wordList = FactoryWordList::create($this->teacherOne)->addRows(typesPerRow: 4)->wordList;
//
//        $word = $wordList->words->first();
//        $testFactory->addQuestions([FactoryQuestionRelation::create()->useLists([$wordList])])->getTestModel();
//        $relationQuestion = RelationQuestion::first();
//
//        VersionManager::getVersionable($wordList, $this->teacherTwo)->editWord($word, ['text' => 'Unit test word']);;
//
//        Livewire::test(
//            WordListChangesModal::class,
//            [
//                'wordData'             => $relationQuestion->getQuestionWordsForCms(),
//                'relationQuestionUuid' => $relationQuestion->uuid,
//                'testUuid'             => $testFactory->getTestModel()->uuid,
//            ]
//        )
//            ->call('acceptChanges')
//            ->assertEmittedTo(Constructor::class, 'relation-question-accepted-word-list-changes');
//    }
}
