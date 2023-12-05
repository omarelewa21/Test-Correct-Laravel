<?php

namespace tcCore\Services;

use Illuminate\Support\Collection;
use tcCore\Http\Enums\WordType;
use tcCore\Observers\VersionableObserver;
use tcCore\RelationQuestion;
use tcCore\Lib\Models\VersionManager;
use tcCore\User;
use tcCore\Word;
use tcCore\WordList;


class CompileWordListService
{

    private array $incomingUpdates = [];
    private array $wordUpdates = [];
    private array $relationQuestionWords = [];
    private array $newSubjectWords = [];

    public function __construct(
        private readonly User     $user,
        private ?Collection       $wordLists,
        private ?RelationQuestion $relationQuestion,
    ) {
        if (!$wordLists) {
            $this->wordLists = collect();
        }
    }

    public static function buildWordItem($word, $list = null): array
    {
        return static::buildEmptyWordItem(
            $word->text,
            $word->type,
            $word->getKey(),
            $list?->getKey()
        );
    }

    public static function buildEmptyWordItem(
        string   $text,
        WordType $type,
        ?int     $wordId = null,
        ?int     $wordListId = null,
    ): array {
        return [
            'text'         => $text,
            'word_id'      => $wordId,
            'word_list_id' => $wordListId,
            'type'         => $type
        ];
    }

    public function categorizeWordUpdatesInActions(): static
    {
        $this->wordLists->each(function ($list) {
            if (!isset($this->incomingUpdates[$list->getKey()])) {
                return true;
            }

            foreach ($this->incomingUpdates[$list->getKey()]['rows'] as $rowKey => $row) {
                $row = $this->prepareRowForCategorizing($row, $rowKey);

                if ($this->isEntireNewRow($row)) {
                    $this->wordUpdates[$list->getKey()]['newRows'][] = $row->map(function ($word) {
                        return ['text' => $word['text'], 'type' => $word['type']];
                    });

                    return true;
                }

                $this->sortProposalsInCategory($row, $list);
            }
        });


        return $this;
    }

    public function performWordActions(): static
    {
        if (collect($this->wordUpdates)->flatten()->isEmpty()) {
            return $this;
        };

        foreach ($this->wordUpdates as $listId => $updates) {
            $list = $this->getListToUpdate($listId);
            VersionableObserver::setMassUpdating($list->getKey(), WordList::class);

            $this->createWords($updates, $list);
            $this->editWords($updates, $list);
            $this->deleteWords($updates, $list);
            $this->createRows($updates, $list);

            VersionableObserver::clearMassUpdating($list->getKey(), WordList::class);
        }

        return $this;
    }

    public function compileRelationQuestionAnswersList(): static
    {
        $this->wordLists
            ->each(function ($list) {
                if (!isset($this->incomingUpdates[$list->getKey()])) {
                    return true;
                }

                $enabled = collect($this->incomingUpdates[$list->getKey()]['enabled']);

                $items = $list->rows(true)
                    ->map(function ($row, $key) use ($list, $enabled) {
                        if ($enabled->doesntContain($key)) {
                            return null;
                        }

                        return $row->map(function ($word) use ($list) {
                            return [
                                'text'         => $word->text,
                                'word_id'      => $word->getKey(),
                                'word_list_id' => $list->getKey(),
                                'selected'     => $word->type === WordType::SUBJECT,
                                'type'         => $word->type
                            ];
                        });
                    })
                    ->filter()
                    ->toArray();
                array_push($this->relationQuestionWords, ...$items);
            });

        return $this;
    }

    public function getRelationQuestionAnswerList(): array
    {
        return $this->relationQuestionWords;
    }

    private function isDeleted($word, WordList $list): bool
    {
        if (!$word['word_id'] || !blank($word['text'])) {
            return false;
        }

        $this->wordUpdates[$list->getKey()]['deleted'][] = $word;
        return true;
    }

    private function isAdded($word, $row, WordList $list): bool
    {
        if (blank($word['text'])) {
            return false;
        }

        if ($word['word_id'] && $word['word_list_id']) {
            return false;
        }

        if ($word['type'] === WordType::SUBJECT) {
            $this->newSubjectWords[$word['row_key']] = true;
        }

        if ($word['type'] !== WordType::SUBJECT
            && $subjectWord = $row->first(fn($word) => $word['type'] === WordType::SUBJECT)) {
            $word['subject_word_id'] = $subjectWord['word_id'];
        }

        $this->wordUpdates[$list->getKey()]['added'][] = $word;
        return true;
    }

    private function isEdited($word, WordList $list): bool
    {
        $existingWordInList = $list->words->where('id', $word['word_id'])?->first();
        if ($this->wordIsNotEdited($existingWordInList, $word)) {
            return false;
        }

        if ($word['type'] === WordType::SUBJECT && $existingWordInList->type !== WordType::SUBJECT) {
            $this->newSubjectWords[$word['row_key']] = $existingWordInList->getKey();
        }

        $this->wordUpdates[$list->getKey()]['edited'][] = [
            'existing_word' => $existingWordInList,
            'word'          => $word
        ];
        return true;
    }

    private function isEntireNewRow(Collection $row): bool
    {
        return $row->count() >= 2 && $row->whereNull('word_id')->count() === $row->count();
    }

    private function sortProposalsInCategory(Collection $row, WordList $list): void
    {
        $row->each(function ($word) use ($row, $list) {
            if ($this->isDeleted($word, $list)) {
                return true;
            };

            if ($this->isAdded($word, $row, $list)) {
                return true;
            }

            if ($this->isEdited($word, $list)) {
                return true;
            };

            /* No change detected */
            return true;
        });
    }


    private function getListToUpdate($listId): WordList
    {
        $list = VersionManager::getVersionable(
            $this->wordLists->where('id', $listId)->first(),
            $this->user
        );

        if ($list->needsDuplication($this->relationQuestion)) {
            $list = $list->handleDuplication();
        }

        if ($listId !== $list->getKey()) {
            $this->wordLists = $this->wordLists->replace([
                $this->wordLists->search(fn($list) => $list->getKey() === $listId) => $list
            ]);

            $this->incomingUpdates[$list->getKey()] = $this->incomingUpdates[$listId];
            unset($this->incomingUpdates[$listId]);
        }

        return $list;
    }


    private function wordIsNotEdited(?Word $existingWordInList, $word): bool
    {
        if (isset($this->newSubjectWords[$word['row_key']])) {
            return false;
        }

        if (!$existingWordInList) {
            return true;
        }

        if (!$word['word_id']) {
            return true;
        }

        if ($word['text'] === $existingWordInList->text && $word['type'] === $existingWordInList->type) {
            return true;
        }

        return false;
    }

    private function getWordUpdatesForEditing(array $editUpdate): array
    {
        $wordUpdate = [];
        $existingWord = $editUpdate['existing_word'];
        $newWord = $editUpdate['word'];

        if ($existingWord->text !== $newWord['text']) {
            $wordUpdate['text'] = $newWord['text'];
        }
        if ($existingWord->type !== $newWord['type']) {
            $wordUpdate['type'] = $newWord['type'];
        }
        if ($existingWord->type !== WordType::SUBJECT && $newWord['type'] === WordType::SUBJECT) {
            $wordUpdate['word_id'] = null;
        }
        if (isset($this->newSubjectWords[$newWord['row_key']]) && $newWord['type'] !== WordType::SUBJECT) {
            $wordUpdate['word_id'] = $this->newSubjectWords[$newWord['row_key']];
        }

        return $wordUpdate;
    }

    private function getSubjectWordForCreating(array $word, WordList $list): ?Word
    {
        $subjectWord = null;
        if (isset($word['subject_word_id'])) {
            $subjectWord = $list->words->where('id', $word['subject_word_id'])?->first();
        }
        return $subjectWord;
    }

    private function createWords(array $updates, WordList $list): void
    {
        if (empty($updates['added'])) {
            return;
        }
        foreach ($updates['added'] as $word) {
            if ($word['word_id']) {
                $newWord = $list->addWord(Word::find($word['word_id']));

                $this->newSubjectWords[$word['row_key']] = $newWord->getKey();
                continue;
            }


            $newWord = $list->createWord(
                $word['text'],
                $word['type'],
                $this->getSubjectWordForCreating($word, $list)
            );
            $this->newSubjectWords[$word['row_key']] = $newWord->getKey();
        }
    }

    private function editWords(array $updates, WordList $list): void
    {
        if (empty($updates['edited'])) {
            return;
        }
        foreach ($updates['edited'] as $editUpdate) {
            $list->editWord(
                $editUpdate['existing_word'],
                $this->getWordUpdatesForEditing($editUpdate)
            );
        }
    }

    private function deleteWords(array $updates, WordList $list): void
    {
        if (empty($updates['deleted'])) {
            return;
        }

        foreach ($updates['deleted'] as $word) {
            $list->removeWord(Word::find($word['word_id']));
        }
    }

    private function createRows(array $updates, WordList $list): void
    {
        if (empty($updates['newRows'])) {
            return;
        }

        foreach ($updates['newRows'] as $newRow) {
            $list->createRow(...$newRow);
        }
    }

    private function prepareRowForCategorizing(array|Collection $row, int $rowKey): Collection
    {
        return collect($row)
            ->map(function ($word) use ($rowKey) {
                $word['type'] = WordType::tryFrom($word['type']);
                $word['row_key'] = $rowKey;
                return $word;
            })
            ->sortBy(fn($word) => $word['type']->getOrder() ?? 10)
            ->values();
    }

    public function updatesToProcess($updates): static
    {
        /*
         * $updates should contain an array of the following structure:
         *  [
         *      <list_id> => [
         *          'name'      => <name>,
         *          'rows'      => [<word rows>],
         *          'enabled'   => [<enabled row indexes>]
         *      ]
         *      ...
         *  ]
         *
         * */

        $this->incomingUpdates = array_filter($updates);

        return $this;
    }

    private function updateName(WordList $list): void
    {
        $name = trim($this->incomingUpdates[$list->getKey()]['name']);
        if ($name === $list->name) {
            return;
        }

        $list->name = $name;
        $list->save();
    }

    public function handleNameChanges(): static
    {
        foreach ($this->incomingUpdates as $listId => $update) {
            $existingList = $this->wordLists->first(fn($wl) => $wl->getKey() === $listId);
            if ($existingList->name !== $update['name']) {
                $listToUpdate = $this->getListToUpdate($listId);
                $listToUpdate->name = $update['name'];
                $listToUpdate->save();
            }
        }

        return $this;
    }
}