<?php

namespace tcCore\Services;

use Illuminate\Support\Collection;
use tcCore\Http\Enums\WordType;
use tcCore\RelationQuestion;
use tcCore\Lib\Models\VersionManager;
use tcCore\User;
use tcCore\Versionable;
use tcCore\Word;
use tcCore\WordList;

use function Composer\Autoload\includeFile;

class CompileWordListService
{

    private array $wordUpdates = [];

    public function __construct(
        private readonly User     $user,
        private ?Collection       $wordLists,
        private ?RelationQuestion $relationQuestion,
    ) {
        if (!$wordLists) {
            $this->wordLists = collect();
        }
    }

    public function categorizeUpdates($updates): static
    {
        $this->wordLists
            ->each(function ($list) use ($updates) {
                $updateRows = collect($updates[$list->getKey()]['rows']);

                $updateRows->each(function ($row) use ($list) {
                    $row = collect($row);
                    if ($this->isEntireNewRow($row)) {
                        $this->wordUpdates[$list->getKey()]['newRows'][] = $row->map(function ($word) {
                            return ['text' => $word['text'], 'type' => WordType::tryFrom($word['type'])];
                        });

                        return true;
                    }

                    $this->sortProposalsInCategory($row, $list);
                });
            });


        return $this;
    }

    public function performChanges(): static
    {
        if (collect($this->wordUpdates)->flatten()->isEmpty()) {
            return $this;
        };

        foreach ($this->wordUpdates as $listId => $updates) {
            $list = VersionManager::getVersionable(
                $this->wordLists->where('id', $listId)->first(),
                $this->user
            );
            if ($list->needsDuplication($this->relationQuestion)) {
                $list = $list->handleDuplication();
            };

            if (!empty($updates['added'])) {
                foreach ($updates['added'] as $word) {
                    $list->createWord($word['text'], $word['type'], $word['subject_word_id'] ?? null);
                }
            }

            if (!empty($updates['edited'])) {
                foreach ($updates['edited'] as $editUpdate) {
                    $wordUpdate = [];
                    if ($editUpdate['existing_word']->text !== $editUpdate['word']['text']) {
                        $wordUpdate['text'] = $editUpdate['word']['text'];
                    }
                    if ($editUpdate['existing_word']->type !== $editUpdate['word']['type']) {
                        $wordUpdate['type'] = $editUpdate['word']['type'];
                    }

                    $list->editWord(
                        $editUpdate['existing_word'],
                        $wordUpdate
                    );
                }
            }

            if (!empty($updates['deleted'])) {
                foreach ($updates['deleted'] as $word) {
                    $list->removeWord(Word::find($word['word_id']));
                }
            }
            if (!empty($updates['newRows'])) {
                foreach ($updates['newRows'] as $newRow) {
                    $list->createRow(...$newRow);
                }
            }
        }

        return $this;
    }

    public function syncWordsToRelationQuestion() {}

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
        if ($word['word_id'] || !filled($word['text'])) {
            return false;
        }

        if ($word['type'] !== WordType::SUBJECT) {
            $word['subject_word_id'] = $row->first()['word_id'];
        }

        $this->wordUpdates[$list->getKey()]['added'][] = $word;
        return true;
    }

    private function isEdited($word, WordList $list): bool
    {
        $existingWordInList = $list->words->where('id', $word['word_id'])?->first();
        if (!$word['word_id'] || ($word['text'] === $existingWordInList?->text && $word['type'] === $existingWordInList->type)) {
            return false;
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

    private function sortProposalsInCategory(Collection $row, $list): void
    {
        collect($row)
            ->sortBy(fn($word) => WordType::tryFrom($word['type'])?->getOrder() ?? 10)
            ->each(function ($word) use (&$added, &$edited, &$deleted, $row, $list) {
                if (!$word['type']) {
                    return true;
                }

                $word['type'] = WordType::tryFrom($word['type']);
                if ($this->isDeleted($word, $list)) {
                    return true;
                };

                if ($this->isAdded($word, $row, $list)) {
                    return true;
                }

                if ($this->isEdited($word, $list/*VersionManager::getVersionable($list, $this->user)*/)) {
                    return true;
                };

                /* No change detected */
                return true;
            });
    }
}