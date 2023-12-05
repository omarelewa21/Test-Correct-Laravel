<?php

namespace Tests\Services;

use Illuminate\Validation\ValidationException;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryUser;
use tcCore\Factories\FactoryWordList;
use tcCore\Factories\Questions\FactoryQuestionRelation;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Enums\WordType;
use tcCore\RelationQuestion;
use tcCore\RelationQuestionWord;
use tcCore\SchoolLocation;
use tcCore\Services\CompileWordListService;
use tcCore\Test;
use tcCore\User;
use tcCore\Word;
use tcCore\WordList;
use tcCore\WordListWord;
use Tests\ScenarioLoader;
use Tests\TestCase;

class CompileWordListServiceTest extends TestCase
{

    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    private User $teacherOne;

    private Test $test;
    private WordList $wordList;
    private RelationQuestion $relationQuestion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacherOne = ScenarioLoader::get('teacher1');
        $this->wordList = FactoryWordList::create($this->teacherOne)->addRows(5, 3)->wordList;
        $this->test = FactoryTest::create($this->teacherOne)
            ->addQuestions([FactoryQuestionRelation::create()->useLists([$this->wordList])])
            ->getTestModel();
        $this->relationQuestion = RelationQuestion::first();
    }

    /** @test */
    public function when_no_words_change_the_updated_at_value_stays_the_same()
    {
        $updateRequest = $this->convertQuestionListsToUpdateRequest($this->relationQuestion);
        $latestUpdate = Word::latest('updated_at')->value('updated_at');

        $compileService = new CompileWordListService(
            $this->teacherOne,
            $this->relationQuestion->wordLists,
            $this->relationQuestion
        );

        $compileService->categorizeWordUpdatesInActions($updateRequest)
            ->performWordActions();

        $this->assertEquals(
            Word::latest('updated_at')->value('updated_at'),
            $latestUpdate
        );
    }

    /** @test */
    public function can_change_words_by_feeding_the_compile_service()
    {
        $this->assertTrue(Word::whereText('Koekjes')->doesntExist());
        $databaseCount = Word::count();

        $updateRequest = $this->convertQuestionListsToUpdateRequest($this->relationQuestion);

        $this->changeWordTo(
            $updateRequest,
            $this->wordList,
            0,
            'translation',
            'Koekjes'
        );

        $compileService = new CompileWordListService(
            $this->teacherOne,
            $this->relationQuestion->wordLists,
            $this->relationQuestion
        );

        $compileService
            ->updatesToProcess($updateRequest)
            ->categorizeWordUpdatesInActions()
            ->performWordActions();

        $this->assertTrue(Word::whereText('Koekjes')->exists());
        $this->assertEquals($databaseCount, Word::count());
    }

    /** @test */
    public function can_add_words_by_feeding_the_compile_service()
    {
        $this->assertTrue(Word::whereText('Koekjes')->doesntExist());
        $databaseCount = Word::count();

        $updateRequest = $this->convertQuestionListsToUpdateRequest($this->relationQuestion);

        $this->addWordTo(
            $updateRequest,
            $this->wordList,
            0,
            WordType::DEFINITION->value,
            'Koekjes'
        );

        $compileService = new CompileWordListService(
            $this->teacherOne,
            $this->relationQuestion->wordLists,
            $this->relationQuestion
        );

        $compileService
            ->updatesToProcess($updateRequest)
            ->categorizeWordUpdatesInActions()
            ->performWordActions();

        $this->assertTrue(Word::whereText('Koekjes')->exists());
        $this->assertEquals($databaseCount + 1, Word::count());
    }

    /** @test */
    public function can_delete_words_by_feeding_the_compile_service()
    {
        $updateRequest = $this->convertQuestionListsToUpdateRequest($this->relationQuestion);
        $translation = $updateRequest[$this->wordList->getKey()]['rows'][0]['translation'];
        $this->assertTrue(
            $this->wordList->words()->whereText($translation['text'])->exists()
        );

        $this->deleteWordFrom(
            $updateRequest,
            $this->wordList,
            0,
            'translation'
        );

        $compileService = new CompileWordListService(
            $this->teacherOne,
            $this->relationQuestion->wordLists,
            $this->relationQuestion
        );

        $compileService
            ->updatesToProcess($updateRequest)
            ->categorizeWordUpdatesInActions()
            ->performWordActions();

        $this->assertTrue(
            $this->wordList->words()->whereText($translation['text'])->doesntExist()
        );
    }

    /** @test
     *
     * /* Types have shuffled and a new Subject word is added to a row with existing words;
     *  f.e.:
     *  Subject | Translation | Synonym
     *  wordA                    wordB
     *
     *  became
     *  Translation | Subject | Synonym
     *  WordA         WordC      WordB
     * /
     */
    public function can_create_new_subject_word_and_link_existing_ones_to_it()
    {
        $wordToRemove = $this->wordList->words()->where('type', WordType::TRANSLATION)->get()->get(4);
        Word::where('word_id', $wordToRemove->getKey())->delete();
        WordListWord::where('word_id', $wordToRemove->getKey())->where(
            'word_list_id',
            $this->wordList->getKey()
        )->delete();
        RelationQuestionWord::where('word_id', $wordToRemove->getKey())->delete();

        $updateRequest = $this->convertQuestionListsToUpdateRequest($this->relationQuestion);

        $updateRequest[$this->wordList->getKey()]['rows'] = collect($updateRequest[$this->wordList->getKey()]['rows'])
            ->map(function ($row) {
                return collect($row)->mapWithKeys(function ($word, $type) {
                    if ($type === 'subject') {
                        $word['type'] = 'translation';
                    } elseif ($type === 'translation') {
                        $word['type'] = 'subject';
                    }

                    return [$word['type'] => $word];
                });
            });

        $compileService = new CompileWordListService(
            $this->teacherOne,
            $this->relationQuestion->wordLists,
            $this->relationQuestion
        );

        $compileService
            ->updatesToProcess($updateRequest)
            ->categorizeWordUpdatesInActions()
            ->performWordActions();

        $this->assertTrue(true);
    }

    /** @test */
    public function can_trigger_row_validation_rule()
    {
        $this->expectException(ValidationException::class);
        $compileService = new CompileWordListService(
            $this->teacherOne,
            $this->relationQuestion->wordLists,
        );

        $this->updateRequest[1]['rows'][0][0]['type'] = 'cake';

        $compileService->updatesToProcess($this->updateRequest)
            ->validateUpdates();
    }

    /** @test */
    public function can_handle_updates_from_another_teacher()
    {
        $d2 = FactoryUser::createTeacher(SchoolLocation::first(), false)->user;
        $updateRequest = $this->convertQuestionListsToUpdateRequest($this->relationQuestion);
        $wordUpdate = 'd2 tekst';
        $updateRequest[1]['rows'][0]['translation']['text'] = $wordUpdate;

        $this->actingAs($d2);

        $relationQuestionAnswerList = (new CompileWordListService(
            $d2,
            $this->relationQuestion->wordLists,
            $this->relationQuestion
        ))
            ->updatesToProcess($updateRequest)
            ->categorizeWordUpdatesInActions()
            ->performWordActions()
            ->compileRelationQuestionAnswersList()
            ->getRelationQuestionAnswerList();

        $this->assertEquals(
            $wordUpdate,
            $relationQuestionAnswerList[0][1]['text']
        );
    }

    /** @test */
    public function can_handle_updates_from_another_teacher_pt2()
    {
        $d2 = FactoryUser::createTeacher(SchoolLocation::first(), false)->user;
        $updateRequest = $this->convertQuestionListsToUpdateRequest($this->relationQuestion);
        $wordUpdate = 'd2 tekst';
        $updateRequest[1]['rows'][0]['subject']['text'] = $wordUpdate;

        $this->actingAs($d2);

        $relationQuestionAnswerList = (new CompileWordListService(
            $d2,
            $this->relationQuestion->wordLists,
            $this->relationQuestion
        ))
            ->updatesToProcess($updateRequest)
            ->categorizeWordUpdatesInActions()
            ->performWordActions()
            ->compileRelationQuestionAnswersList()
            ->getRelationQuestionAnswerList();

        /* TODO: Maybe keep a fixed order when updating...
         * Now when updating the first word, it becomes last because its a new one
         * */
        $this->assertEquals(
            $wordUpdate,
            $relationQuestionAnswerList[count($relationQuestionAnswerList) - 1][0]['text']
        );
    }

    private function convertQuestionListsToUpdateRequest(RelationQuestion $question): array
    {
        $updateRequest = [];

        foreach ($question->wordLists as $list) {
            $updateRequest[$list->getKey()]['name'] = $list->name;
            $updateRequest[$list->getKey()]['rows'] = $list->rows(true)
                ->map(function ($row, $key) use ($list) {
                    return collect(WordType::cases())
                        ->mapWithKeys(function ($type) use ($row, $key, $list) {
                            $word = $row->first(fn($word) => $word->type === $type);

                            if (!$word) {
                                return [];
                            }
                            $wordItem = CompileWordListService::buildWordItem($word, $list);
                            $wordItem['type'] = $wordItem['type']->value;
                            $wordItem['row_key'] = $key;
                            return [$type->value => $wordItem];
                        })->filter();
                })->toArray();
            $updateRequest[$list->getKey()]['enabled'] = range(0, $list->rows()->count() - 1);
        }


        return $updateRequest;
    }

    public array $updateRequest = [
        /*list id*/
        1 => [
            "name"    => 'Hallo',
            "rows"    => [
                [
                    [
                        "text"         => "provident",
                        "word_id"      => 1,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "dolor",
                        "word_id"      => 2,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "et",
                        "word_id"      => 3,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "nostrum",
                        "word_id"      => 4,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "recusandae",
                        "word_id"      => 5,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "ab",
                        "word_id"      => 6,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "et",
                        "word_id"      => 7,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "consequatur",
                        "word_id"      => 8,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "sapiente",
                        "word_id"      => 9,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "ko",
                        "word_id"      => 10,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "floepje",
                        "word_id"      => 65,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "occaecati",
                        "word_id"      => 12,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "quia",
                        "word_id"      => 13,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "error",
                        "word_id"      => 14,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "koekie",
                        "word_id"      => 66,
                        "word_list_id" => 1,
                        "type"         => "synonym"
                    ]
                ],
                [
                    [
                        "text"         => "eveniet",
                        "word_id"      => 16,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "nesciunt",
                        "word_id"      => 17,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "quas",
                        "word_id"      => 18,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "similique",
                        "word_id"      => 19,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "et",
                        "word_id"      => 20,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "pariatur",
                        "word_id"      => 21,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "et",
                        "word_id"      => 22,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "voluptatem",
                        "word_id"      => 23,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "eum",
                        "word_id"      => 24,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "dolorum",
                        "word_id"      => 25,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "expedita",
                        "word_id"      => 26,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "ad",
                        "word_id"      => 27,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "reprehenderit",
                        "word_id"      => 28,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "cumque",
                        "word_id"      => 29,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "et",
                        "word_id"      => 30,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "aliquid",
                        "word_id"      => 31,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "sunt",
                        "word_id"      => 32,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "at",
                        "word_id"      => 33,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "veniam",
                        "word_id"      => 34,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "nostrum",
                        "word_id"      => 35,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "eaque",
                        "word_id"      => 36,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "perspiciatis",
                        "word_id"      => 37,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "autem",
                        "word_id"      => 38,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "iure",
                        "word_id"      => 39,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "autem",
                        "word_id"      => 40,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "molestiae",
                        "word_id"      => 41,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "eos",
                        "word_id"      => 42,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "ratione",
                        "word_id"      => 43,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "minus",
                        "word_id"      => 44,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "magni",
                        "word_id"      => 45,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "maxime",
                        "word_id"      => 46,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "rem",
                        "word_id"      => 47,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "accusantium",
                        "word_id"      => 48,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "nulla",
                        "word_id"      => 49,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "ex",
                        "word_id"      => 50,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "floep",
                        "word_id"      => 64,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "voluptas",
                        "word_id"      => 52,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "sapiente",
                        "word_id"      => 53,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "labore",
                        "word_id"      => 54,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "sunt",
                        "word_id"      => 55,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "assumenda",
                        "word_id"      => 57,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ],
                [
                    [
                        "text"         => "eum",
                        "word_id"      => 58,
                        "word_list_id" => 1,
                        "type"         => "subject"
                    ],
                    [
                        "text"         => "porro",
                        "word_id"      => 59,
                        "word_list_id" => 1,
                        "type"         => "translation"
                    ],
                    [
                        "text"         => "id",
                        "word_id"      => 60,
                        "word_list_id" => 1,
                        "type"         => "definition"
                    ]
                ]
            ],
            "enabled" => [
                0,
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15,
                16,
                17,
                18,
                19
            ]
        ]
    ];

    private function changeWordTo(
        array    &$updateRequest,
        WordList $list,
        int      $rowIndex,
        string   $type,
        string   $text
    ): void {
        $updateRequest[$list->getKey()]['rows'][$rowIndex][$type]['text'] = $text;
    }

    private function addWordTo(
        array    &$updateRequest,
        WordList $list,
        int      $rowIndex,
        string   $type,
        string   $text
    ): void {
        $updateRequest[$list->getKey()]['rows'][$rowIndex][$type] = [
            'text'         => $text,
            'word_id'      => null,
            'word_list_id' => $list->getKey(),
            'type'         => $type,
            'row_key'      => $rowIndex
        ];
    }

    private function deleteWordFrom(array &$updateRequest, WordList $list, int $rowIndex, string $type): void
    {
        $updateRequest[$list->getKey()]['rows'][$rowIndex][$type]['text'] = null;
    }
}
