<?php

namespace tcCore\Http\Livewire\Teacher\Cms;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use tcCore\Http\Enums\WordType;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Lib\Models\VersionManager;
use tcCore\RelationQuestion;
use tcCore\Services\CompileWordListService;
use tcCore\Test;
use tcCore\Versionable;
use tcCore\Word;
use tcCore\WordList;

class CompileWordListModal extends TCModalComponent
{
    public array $columnHeads;
    public array $wordListUuids = [];

    private Collection $wordLists;
    public array $wordData;
    public string $testUuid;

    public ?RelationQuestion $relationQuestion = null;

    public function mount(
        array             $wordData,
        string            $testUuid,
        ?RelationQuestion $relationQuestion = null,
    ) {
        $this->columnHeads = WordType::casesWithDescription()->toArray();
        $this->relationQuestion = $relationQuestion;

        $this->setWordLists(
            WordList::whereIn('id', collect($wordData)->pluck('subject.word_list_id')->unique()),
            $wordData
        );
    }

    public function render()
    {
        return view('livewire.teacher.cms.compile-word-list-modal');
    }

    public function hydrateWordListUuids()
    {
        $this->setWordLists(
            WordList::whereUuidIn($this->wordListUuids),
            $this->wordData
        );
    }

    public static function modalMaxWidthClass(): string
    {
        return 'modal-full-screen';
    }

    private function setWordLists(Builder $lists, array $wordData): void
    {
        $wordData = collect($wordData);
        $enabledRows = $wordData->pluck('subject.word_id');

        $this->wordLists = $lists
            ->with('words')
            ->get()
            ->each(fn($list) => $this->setRowPropertyOnList($list, $enabledRows));

        $this->wordListUuids = $this->wordLists->pluck('uuid')->toArray();
    }

    private function addToEnabledRowsWhenUsed(Collection $enabledRows, $row, WordList $list, int $key): void
    {
        if ($enabledRows->contains($row->first()->getKey())) {
            $list->enabledRows = array_merge($list->enabledRows ?? [], [$key]);
        }
    }

    public function createNewList(): array
    {
        $test = Test::whereUuid($this->testUuid)->first();
        $newList = WordList::build(
            name              : sprintf('Woordenlijst %s', $test->name),
            author            : auth()->user(),
            subjectId         : $test->subject_id,
            educationLevelId  : $test->education_level_id,
            educationLevelYear: $test->education_level_year,
            schoolLocationId  : auth()->user()->school_location_id
        );

        $this->setRowPropertyOnList($newList, collect());
        $this->wordListUuids[] = $newList->uuid;

        return $newList->toArray();
    }

    public function compile($updates): bool
    {
        $compileService = (new CompileWordListService(
            auth()->user(),
            $this->cleanWordLists(),
            $this->relationQuestion
        ))
            ->updatesToProcess($updates)
            ->handleNameChanges()
            ->categorizeWordUpdatesInActions()
            ->performWordActions()
            ->compileRelationQuestionAnswersList();

        /*Stap 2*/
        $this->closeModalWithEvents([
            Constructor::class => [
                'relation-question-words-updated',
                [$compileService->getRelationQuestionAnswerList()]
            ]
        ]);

        return true;
    }

    private function setRowPropertyOnList(WordList $list, Collection $enabledRows): void
    {
        $list->enabledRows = [];
        $list->rows = $list->rows()->map(function ($row, $key) use ($list, $enabledRows) {
            $this->addToEnabledRowsWhenUsed($enabledRows, $row, $list, $key);

            return collect(WordType::cases())
                ->mapWithKeys(function ($type) use ($row, $list) {
                    $word = $row->first(fn($word) => $word->type === $type);

                    return $word ? [$type->value => CompileWordListService::buildWordItem($word, $list)] : [];
                })
                ->filter();
        });
    }

    private function cleanWordLists(): Collection
    {
        $this->wordLists->each(function ($list) {
            unset($list->enabledRows);
            unset($list->rows);
        });

        return $this->wordLists;
    }

    public function addExistingWordList(string $uuid): array
    {
        $list = WordList::whereUuid($uuid)->first();
        if (!$list) {
            return [];
        }

        $list = VersionManager::getVersionable($list, auth()->user());
        $this->setRowPropertyOnList($list, collect());
        $this->wordListUuids[] = $list->uuid;

        return $list->toArray();
    }
}
