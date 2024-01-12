<?php

namespace tcCore\Http\Livewire\Teacher\Cms;

use Illuminate\Support\Collection;
use tcCore\Http\Enums\WordType;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\RelationQuestion;
use tcCore\Services\CompileWordListService;
use tcCore\Word;
use tcCore\WordList;

class WordListChangesModal extends TCModalComponent
{
    public array $wordData;
    public string $testUuid;
    public string $relationQuestionUuid;

    protected Collection $wordLists;
    public array $columnHeads;

    public function mount(
        $wordData,
        $testUuid,
        $relationQuestionUuid,
    ): void {
        $relationQuestion = RelationQuestion::whereUuid($relationQuestionUuid)
            ->with(['wordLists', 'wordLists.words'])
            ->first();

        $this->columnHeads = RelationQuestion::columnHeads($relationQuestion->subject);

        $this->wordLists = $relationQuestion->wordLists
            ->filter(fn($list) => $list->hasNewVersion())
            ->map(function ($list) use ($wordData) {
                $this->setRowPropertyOnList($list, collect($wordData)->pluck('subject.word_id'));

                $diff = $list->getDiff();
                $this->markUpdates($diff, $list);
                $this->markDeletions($diff, $list);
                $this->addAndMarkAdditions($diff, $list);

                return $list;
            });
    }

    private function setRowPropertyOnList(
        WordList   $list,
        Collection $enabledRows,
    ): void {
        $list->enabledRows = [];
        $list->rows = $list->rows()->map(function ($row, $key) use ($list, $enabledRows) {
            $this->addToEnabledRowsWhenUsed($enabledRows, $row, $list, $key);

            return collect(WordType::cases())
                ->mapWithKeys(function ($type) use ($row, $list) {
                    $word = $row->first(fn($word) => $word->type === $type);

                    return $word
                        ? [$type->value => CompileWordListService::buildWordItem($word, $list) + ['color' => null]]
                        : [];
                })
                ->filter();
        });
    }

    private function addToEnabledRowsWhenUsed(Collection $enabledRows, $row, WordList $list, int $key): void
    {
        if ($enabledRows->contains($row->first()->getKey())) {
            $list->enabledRows = array_merge($list->enabledRows ?? [], [$key]);
        }
    }

    public static function modalMaxWidthClass(): string
    {
        return 'modal-full-screen';
    }

    private function markUpdates(Collection $diff, WordList $list): void
    {
        foreach ($diff->get('updated') as $update) {
            foreach ($list->rows as $key => $row) {
                collect($row)
                    ->where('word_id', $update->original_id)
                    ->each(function ($victim, $type) use ($key, $list, $update) {
                        if ($victim['text'] === $update->text) {
                            return true;
                        }

                        $victim['text'] = $update->text;
                        $victim['color'] = 'blue';

                        $list->rows[$key][$type] = $victim;
                    });
            }
        }
    }

    private function markDeletions(Collection $diff, WordList $list): void
    {
        foreach ($diff->get('deleted') as $deleted) {
            foreach ($list->rows as $key => $row) {
                collect($row)
                    ->where('word_id', $deleted->getKey())
                    ->each(function ($victim, $type) use ($key, $list) {
                        $victim['color'] = 'red';

                        $list->rows[$key][$type] = $victim;
                    });
            }
        }
    }

    private function addAndMarkAdditions(Collection $diff, WordList $list): void
    {
        /* TODO: Should this be sort on type so subject words are always added first? */
        foreach ($diff->get('created') as $created) {
            $wordItem = CompileWordListService::buildWordItem($created, $list) + ['color' => 'green'];

            if ($created->isSubjectWord()) {
                $list->rows->push(collect([$created->type->value => $wordItem]));
                continue;
            }

            foreach ($list->rows as $key => $row) {
                if (isset($row['subject']['word_id']) && $row['subject']['word_id'] === $created->word_id) {
                    $list->rows->get($key)->put($created->type->value, $wordItem);
                }
            }
        }
    }

    public function acceptChanges(): bool
    {
        $relationQuestion = RelationQuestion::whereUuid($this->relationQuestionUuid)
            ->with(['wordLists'])
            ->first();

        [$questionWords, $wordDataCollection] = $this->getCurrentWordData($relationQuestion);

        $relationQuestion
            ->wordLists
            ->filter(fn($list) => $list->hasNewVersion())
            ->each(function ($currentList) use (&$wordDataCollection, &$questionWords) {
                $latestList = $currentList->getLatestVersionOfList();

                $wordsForList = $questionWords->where('word_list_id', $currentList->getKey());
                foreach ($wordsForList as $item) {
                    if ($latestList->words->where('original_id', $item['word_id'])->isEmpty()) {
                        $wordDataCollection = $this->updateWordDataCollectionWithRemovedWord(
                            $wordDataCollection,
                            $item
                        );
                        continue;
                    }

                    $updatedWord = $latestList->words->where('original_id', $item['word_id'])->first();
                    if ($updatedWord->isSubjectWord()) {
                        $wordDataCollection = $this->updateWordDataCollectionWithSubjectRow(
                            $wordDataCollection,
                            $latestList,
                            $updatedWord
                        );
                        continue;
                    }

                    $wordDataCollection = $this->updateWordDataCollectionWithLatestListProperties(
                        $wordDataCollection,
                        $updatedWord,
                        $currentList,
                        $latestList,
                    );
                }

                if ($currentList->questions()->doesntExist()) {
                    $currentList->hide();
                }
            });

        $this->closeModalWithEvents([
            Constructor::class => [
                'relation-question-accepted-word-list-changes',
                [$wordDataCollection->toArray(), true]
            ]
        ]);

        $this->wordLists = collect();
        return true;
    }

    public function declineChanges(): bool
    {
        RelationQuestion::whereUuid($this->relationQuestionUuid)
            ->with(['wordLists'])
            ->first()
            ->wordLists
            ->filter(fn($list) => $list->hasNewVersion())
            ->each(function ($currentList) {
                $branchedList = $currentList->handleDuplication(true);

                foreach ($this->wordData as $rowKey => $row) {
                    foreach ($row as $type => $item) {
                        if ($item['word_list_id'] === $currentList->getKey()) {
                            $this->wordData[$rowKey][$type]['word_list_id'] = $branchedList->getKey();
                        }
                    }
                }
            });

        $this->closeModalWithEvents([
            Constructor::class => [
                'relation-question-accepted-word-list-changes',
                [$this->wordData, true]
            ]
        ]);

        $this->wordLists = collect();

        return true;
    }

    private function updateWordDataCollectionWithSubjectRow(
        Collection $wordDataCollection,
        WordList   $latestList,
        Word       $updatedWord
    ): Collection {
        $rowKey = $wordDataCollection->where('subject.word_id', $updatedWord->original_id)->keys()->first();
        $newRow = $this->wordData[$rowKey];
        foreach ($newRow as $type => $data) {
            if ($data['word_list_id'] !== null) {
                $newRow[$type]['word_list_id'] = $latestList->getKey();
            }

            if ($type === $updatedWord->type->value) {
                $newRow[$type]['word_id'] = $updatedWord->getKey();
                $newRow[$type]['text'] = $updatedWord->text;
            }
        }

        $wordDataCollection->put($rowKey, $newRow);
        return $wordDataCollection;
    }

    private function updateWordDataCollectionWithLatestListProperties(
        Collection $wordDataCollection,
        Word       $updatedWord,
        WordList   $currentList,
        WordList   $latestList,
    ): Collection {
        return $wordDataCollection->map(function ($row) use (
            $updatedWord,
            $currentList,
            $latestList
        ) {
            return collect($row)->map(
                function ($item) use ($latestList, $currentList, $updatedWord) {
                    if ($item['word_id'] === $updatedWord->original_id) {
                        $item['word_id'] = $updatedWord->getKey();
                        $item['text'] = $updatedWord->text;
                    }
                    if ($item['word_list_id'] === $currentList->getKey()) {
                        $item['word_list_id'] = $latestList->getKey();
                    }
                    return $item;
                }
            )->toArray();
        });
    }

    private function updateWordDataCollectionWithRemovedWord(Collection $wordDataCollection, array $item): Collection
    {
        return $wordDataCollection->map(function ($row) use ($item) {
            if ($row[$item['type']->value]['word_id'] === $item['word_id']) {
                $row[$item['type']->value] = CompileWordListService::buildEmptyWordItem('', $item['type']);
            }
            return $row;
        });
    }

    private function getCurrentWordData(RelationQuestion $relationQuestion): array
    {
        $questionWords = $relationQuestion
            ->questionWords()
            ->get()
            ->map(function ($questionWord) {
                return CompileWordListService::buildEmptyWordItem(
                    $questionWord->word->text,
                    $questionWord->word->type,
                    $questionWord->word_id,
                    $questionWord->word_list_id,
                    $questionWord->selected,
                );
            });

        $wordDataCollection = collect($this->wordData)->values();

        return [$questionWords, $wordDataCollection];
    }
}
