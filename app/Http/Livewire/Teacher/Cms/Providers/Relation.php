<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use Illuminate\Support\Collection;
use tcCore\Http\Enums\WordType;
use tcCore\RelationQuestion;
use tcCore\Services\CompileWordListService;

class Relation extends TypeProvider
{
    public $requiresAnswer = true;
    public $questionOptions = [
        'decimal_score'           => true,
        'shuffle'                 => false,
        'shuffle_per_participant' => false,
        'selection_count'         => null,
    ];

    public function preparePropertyBag(): void
    {
        parent::preparePropertyBag();

        $this->instance->cmsPropertyBag['rows'] = $this->getEmptyGridRows(18);
        $this->instance->cmsPropertyBag['word_count'] = 0;
        $this->instance->question['uuid'] = null;
        $this->instance->cmsPropertyBag['column_heads'] = RelationQuestion::columnHeads($this->instance->subjectId);
        $this->instance->cmsPropertyBag['unhandled_list_changes'] = false;
    }

    public function initializePropertyBag($question): void
    {
        $this->instance->question['shuffle'] = $question->shuffle;
        $this->instance->question['selection_count'] = $question->selection_count;
        $this->instance->question['uuid'] = $question['uuid'];

        $this->instance->cmsPropertyBag['rows'] = $question->getQuestionWordsForCms();

        $this->handleRowCountDependentAttributes($question->questionWords->count());

        if ($question->wordLists->first(fn($list) => $list->hasNewVersion())) {
            $this->instance->cmsPropertyBag['unhandled_list_changes'] = true;
        }
    }

    public function getTranslationKey(): string
    {
        return __('question.relationquestion');
    }

    public function getTemplate(): string
    {
        return 'relation-question';
    }

    public function hasScoringDisabled(): bool
    {
        return true;
    }

    public function questionSectionTitle(): string
    {
        return __('cms.Begeleidende tekst');
    }

    public function answerSectionTitle(): string
    {
        return __('cms.Vraagstelling met antwoordmodel');
    }

    public function isSettingDisabled(string $property): bool
    {
        return $property === 'decimalScore';
    }

    public function makeUpdates($updates)
    {
        $this->instance->dirty = true;
        if (!$this->instance->question['answer']) {
            $this->instance->question['answer'] = 'not empty';
        }
        $this->instance->skipRender();

        foreach ($updates as $update) {
            $row = collect($this->instance->cmsPropertyBag['rows'][$update['row']]);
            $row = $row->map(function ($item) use ($update) {
                if ($item['word_id'] === $update['word_id']) {
                    $item['selected'] = $update['selected'];
                }
                return $item;
            });

            $this->instance->cmsPropertyBag['rows'][$update['row']] = $row->toArray();
        }
    }

    public function retrieveWords(): array
    {
        return $this->instance->cmsPropertyBag['rows'];
    }

    public function prepareForSave()
    {
        $this->instance->question['score'] = $this->getQuestionScore();
        $this->instance->question['answers'] = collect($this->instance->cmsPropertyBag['rows'])
            ->flatMap(function ($row) {
                return collect($row)
                    ->mapWithKeys(fn($item) => [$item['word_id'] => $item])
                    ->reject(fn($item, $key) => $item['word_id'] === null);
            });
    }

    private function getEmptyGridRows(int $rows): array
    {
        return array_map(fn($row) => RelationQuestion::buildRow($row, true), range(1, $rows));
    }

    public function openCompileListsModal(): void
    {
        $this->instance->emit(
            'openModal',
            'teacher.cms.compile-word-list-modal',
            [
                'wordData'             => $this->rowsWithoutEmptyValues(),
                'testUuid'             => $this->instance->testId,
                'relationQuestionUuid' => $this->instance->question['uuid'],
            ]
        );
    }

    public function openViewWordListChangesModal(): void
    {
        $this->instance->emit(
            'openModal',
            'teacher.cms.word-list-changes-modal',
            [
                'wordData'             => $this->rowsWithoutEmptyValues(),
                'testUuid'             => $this->instance->testId,
                'relationQuestionUuid' => $this->instance->question['uuid'],
            ]
        );
    }

    public function newRelationQuestionWords(array $data, bool $save = false): void
    {
        $this->handleDirtyStateFromWordsUpdate($data);
        $wordCount = 0;

        /*TODO Figure out if the new words contain already existing words with a selected property*/
        $this->instance->cmsPropertyBag['rows'] = collect($data)->map(function ($row) use (&$wordCount) {
            $convertedRow = collect($row)->map(function ($word) use (&$wordCount) {
                $wordCount++;
                $questionWord = (object)$word;
                $questionWord->type = WordType::tryFrom($questionWord->type);
                $questionWord->word = $questionWord;
                return $questionWord;
            });

            return RelationQuestion::buildRow($convertedRow);
        });

        $this->handleRowCountDependentAttributes($wordCount);

        $this->instance->dispatchBrowserEvent(
            'relation-rows-updated',
            $this->instance->cmsPropertyBag['rows']
        );

        if ($save) {
            $this->instance->save(false);
        }
    }

    private function handleRowCountDependentAttributes(int $wordCount): void
    {
        $this->instance->question['score'] = $this->getQuestionScore();
        $this->instance->cmsPropertyBag['word_count'] = $wordCount;

        if (count($this->instance->cmsPropertyBag['rows']) > 0) {
            $this->instance->question['answer'] = 'not empty';
        }

        if (count($this->instance->cmsPropertyBag['rows']) < 18) {
            $rowsToAdd = 18 - count($this->instance->cmsPropertyBag['rows']);
            $this->instance->cmsPropertyBag['rows'] = collect($this->instance->cmsPropertyBag['rows'])
                ->concat($this->getEmptyGridRows($rowsToAdd))
                ->values();
        }
    }

    private function getQuestionScore(): int
    {
        return $this->instance->question['shuffle']
            ? $this->instance->question['selection_count']
            : count($this->rowsWithoutEmptyValues());
    }

    private function handleDirtyStateFromWordsUpdate(array $incomingData): void
    {
        if ($this->rowsWithoutEmptyValues()->count() !== count($incomingData)) {
            $this->instance->dirty = true;
            return;
        }

        $isDirty = false;
        foreach ($this->rowsWithoutEmptyValues() as $rowKey => $row) {
            $row = collect($row)->whereNotIn('text', ['', null]);
            if (collect($row)->whereNotIn('text', ['', null])->count() !== count($incomingData[$rowKey])) {
                $isDirty = true;
                break;
            }

            foreach ($row as $itemKey => $item) {
                if (!isset($incomingData[$rowKey][$itemKey])) {
                    $isDirty = true;
                    break 2;
                }

                $incomingWord = $incomingData[$rowKey][$itemKey];
                if ($item['id'] !== $incomingWord['id'] || $item['text'] !== $incomingWord['text']) {
                    $isDirty = true;
                    break 2;
                }
            }
        }

        if ($isDirty) {
            $this->instance->dirty = true;
        }
    }

    private function rowsWithoutEmptyValues(): Collection
    {
        return collect($this->instance->cmsPropertyBag['rows'])
            ->map(fn($row) => !in_array($row['subject']['text'], [null, '']) ? $row : null)
            ->filter();
    }

    public function updatedQuestionSelectionCount($value): void
    {
        if ($value > count($this->rowsWithoutEmptyValues())) {
            $this->instance->question['selection_count'] = count($this->rowsWithoutEmptyValues());
        }

        $this->instance->question['score'] = $this->instance->question['selection_count'];
    }
}