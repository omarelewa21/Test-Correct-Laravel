<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use tcCore\Http\Enums\WordType;
use tcCore\RelationQuestionWord;
use tcCore\Services\CompileWordListService;

class Relation extends TypeProvider
{
    public $requiresAnswer = true;
    public $questionOptions = [
        'decimal_score'   => true,
        'shuffle'         => true,
        'selection_count' => 5,
    ];

    public function preparePropertyBag()
    {
        parent::preparePropertyBag();

        $this->instance->cmsPropertyBag['rows'] = $this->getEmptyGridRows(18);
        $this->instance->cmsPropertyBag['word_count'] = 0;
        $this->instance->question['uuid'] = null;
    }

    public function initializePropertyBag($q)
    {
        $this->instance->question['shuffle'] = $q->shuffle;
        $this->instance->question['selection_count'] = $q->selection_count;
        $this->instance->question['uuid'] = $q['uuid'];

        $this->instance->cmsPropertyBag['word_count'] = $q->questionWords->count();
        $this->instance->cmsPropertyBag['rows'] = $q->questionWords
            ->sortBy(fn($relation) => $relation->word->type->getOrder())
            ->groupBy(function ($relation) {
                return $relation->word->word_id ?? $relation->word->id;
            })
            ->map(fn($row) => $this->buildRow($row));

        $this->handleRowCountDependentAttributes();
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
        $this->instance->question['answers'] = collect($this->instance->cmsPropertyBag['rows'])
            ->flatMap(function ($row) {
                return collect($row)
                    ->mapWithKeys(fn($item) => [$item['word_id'] => $item])
                    ->reject(fn($item, $key) => $item['word_id'] === null);
            });
    }

    public function buildRow($row, $empty = false): array
    {
        $columns = [];
        foreach (WordType::cases() as $type) {
            $questionWord = $empty ? null : $row?->first(fn($rela) => $rela->word->type === $type);

            $columns[$type->value] = CompileWordListService::buildEmptyWordItem(
                    $questionWord?->word?->text ?? '',
                    $type,
                    $questionWord?->word_id,
                    $questionWord?->word_list_id
                ) + ['selected' => $questionWord?->selected ?? false];
        }
        return $columns;
    }

    private function getEmptyGridRows(int $rows): array
    {
        return array_map(fn($row) => $this->buildRow($row, true), range(1, $rows));
    }

    public function openCompileListsModal(): void
    {
        $wordData = collect($this->instance->cmsPropertyBag['rows'])
            ->map(function ($row) {
                if ($row['subject']['text'] !== null) {
                    return $row;
                }
            })
            ->filter();

        $this->instance->emit(
            'openModal',
            'teacher.cms.compile-word-list-modal',
            [
                'wordData'         => $wordData,
                'testUuid'         => $this->instance->testId,
                'relationQuestion' => $this->instance->question['uuid'],
            ]
        );
    }

    public function newWords($data): void
    {
        $this->instance->dirty = true;
        /*TODO Figure out if the new words contain already existing words with a selected property*/
        $this->instance->cmsPropertyBag['rows'] = collect($data)->map(function ($row) {
            $convertedRow = collect($row)->map(function ($word) {
                $questionWord = (object)$word;
                $questionWord->type = WordType::tryFrom($questionWord->type);
                $questionWord->word = $questionWord;
                return $questionWord;
            });

            return $this->buildRow($convertedRow);
        });

        $this->handleRowCountDependentAttributes();

        $this->instance->dispatchBrowserEvent(
            'relation-rows-updated',
            $this->instance->cmsPropertyBag['rows']
        );
    }


    private function handleRowCountDependentAttributes(): void
    {
        if (count($this->instance->cmsPropertyBag['rows']) > 0) {
            $this->instance->question['answer'] = 'not empty';
        }

        if (count($this->instance->cmsPropertyBag['rows']) < 18) {
            $rowsToAdd = 18 - count($this->instance->cmsPropertyBag['rows']);
            $this->instance->cmsPropertyBag['rows'] = $this->instance->cmsPropertyBag['rows']
                ->concat($this->getEmptyGridRows($rowsToAdd))
                ->values();
        }
    }
}