<?php

namespace tcCore\Http\Livewire\Teacher\Cms;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use tcCore\Http\Enums\WordType;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Imports\WordsImport;
use tcCore\Lib\Models\VersionManager;
use tcCore\RelationQuestion;
use tcCore\Services\CompileWordListService;
use tcCore\Test;
use tcCore\Word;
use tcCore\WordList;

class CompileWordListModal extends TCModalComponent
{
    use WithFileUploads;

    public array $columnHeads;
    public array $wordListUuids = [];
    public array $newLists = [];

    private Collection $wordLists;
    public array $wordData;
    public string $testUuid;
    public $relationQuestionUuid = null;

    public $importFile;

    public function mount(
        array  $wordData,
        string $testUuid,
        mixed  $relationQuestionUuid = null,
    ) {
        $this->columnHeads = CompileWordListService::columnHeads($testUuid);

        $this->setWordLists(
            WordList::whereIn('id', collect($wordData)->pluck('subject.word_list_id')->unique()),
            $wordData
        );
    }

    public function render()
    {
        return view('livewire.teacher.cms.compile-word-list-modal');
    }

    public function hydrateWordListUuids(): void
    {
        $builder = empty($this->wordListUuids) ? null : WordList::whereUuidIn($this->wordListUuids);
        $this->setWordLists(
            $builder,
            $this->wordData
        );
    }

    public static function modalMaxWidthClass(): string
    {
        return 'modal-full-screen';
    }

    private function setWordLists(?Builder $lists, array $wordData): void
    {
        if (!$lists) {
            $this->wordLists = collect();
            return;
        }
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

    public function createNewList(bool $toArray = true): WordList|array
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
        $this->newLists[] = $newList->uuid;

        return $toArray ? $newList->toArray() : $newList;
    }

    public function compile($updates): bool
    {
        $compileService = (new CompileWordListService(
            auth()->user(),
            $this->cleanWordLists(),
            RelationQuestion::whereUuid($this->relationQuestionUuid)->first()
        ))->updatesToProcess($updates);

        try {
            $compileService->validateUpdates();
        } catch (\Exception $e) {
            return false;
        }

        $compileService->handleNameChanges()
            ->categorizeWordUpdatesInActions()
            ->performWordActions()
            ->compileRelationQuestionAnswersList();

        $this->closeModalWithEvents([
            Constructor::class => [
                'relation-question-words-updated',
                [$compileService->getRelationQuestionAnswerList()]
            ]
        ]);

        return true;
    }

    private function setRowPropertyOnList(
        WordList   $list,
        Collection $enabledRows,
        ?bool      $omitListIdOnWordItems = false
    ): void {
        $list->enabledRows = [];
        $list->rows = $list->rows()->map(function ($row, $key) use ($omitListIdOnWordItems, $list, $enabledRows) {
            $this->addToEnabledRowsWhenUsed($enabledRows, $row, $list, $key);

            return collect(WordType::cases())
                ->mapWithKeys(function ($type) use ($omitListIdOnWordItems, $row, $list) {
                    $word = $row->first(fn($word) => $word->type === $type);

                    if (!$word) {
                        return [];
                    }
                    return [
                        $type->value => CompileWordListService::buildWordItem(
                            $word,
                            $omitListIdOnWordItems ? null : $list
                        )
                    ];
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

    /**
     * @param string $uuid
     * @param bool|null $inline => When true, the words from the list are added separately to another list.
     * @return array
     */
    public function addExistingWordList(string $uuid, ?bool $inline = false): array
    {
        $list = WordList::whereUuid($uuid)->first();
        if (!$list) {
            return [];
        }

        $list = VersionManager::getVersionable($list, auth()->user());
        $this->setRowPropertyOnList($list, collect(), $inline);

        if (!$inline) {
            $this->wordListUuids[] = $list->uuid;
        }

        return $list->toArray();
    }

    public function addExistingWord(string $uuid): array
    {
        $subjectWord = Word::whereUuid($uuid)->with('associations')->first();
        if (!$subjectWord) {
            return [];
        }

        $subjectWord = VersionManager::getVersionable($subjectWord, auth()->user());

        $row = collect(WordType::cases())->mapWithKeys(function ($type) use ($subjectWord) {
            if ($type === WordType::SUBJECT) {
                return [$type->value => CompileWordListService::buildWordItem($subjectWord)];
            }

            $word = $subjectWord->associations->first(fn($word) => $word->type === $type);
            return $word ? [$type->value => CompileWordListService::buildWordItem($word)] : [];
        })
            ->filter();

        return $row->toArray();
    }

    public function updatingImportFile(&$value): void
    {
        $fileValidation = Validator::make(['importFile' => $value], ['importFile' => [File::types(['xlsx', 'xls'])]]);
        if ($fileValidation->fails()) {
            $this->setErrorBag($fileValidation->errors());
            $value = null;
        }
    }

    public function importIntoList(bool $newList = false, array $columnOrder = []): array
    {
        $words = $this->import($columnOrder);
        if (!$newList || !$words) {
            return $words;
        }

        $emptyList = $this->createNewList(false);
        $emptyList->rows = $words;

        return $emptyList->toArray();
    }

    private function import($columnOrder = []): array
    {
        $this->validate(['importFile' => ['required', File::types(['xlsx', 'xls'])]]);

        $importer = new WordsImport();
        if ($columnOrder) {
            $importer->setTypeOrder($columnOrder);
        }
        try {
            Excel::import(
                $importer,
                $this->importFile,
            );
        } catch (ValidationException $e) {
            $failedRows = collect();
            foreach ($e->failures() as $failure) {
                $failedRows->push($failure->row());
            }

            $this->addError('import_empty_values', $this->getFailedImportValidationMessage($failedRows));

            return [];
        }

        $this->importFile = null;

        return $importer->getWordList();
    }

    /**
     * @param Collection $failedRows
     * @return string
     */
    private function getFailedImportValidationMessage(Collection $failedRows): string
    {
        return trans_choice(
            'validation.word_import_empty_values',
            $failedRows->count(),
            ['rows' => $failedRows->sort()->join(', ', sprintf(' %s ', __('test-take.and')))]
        );
    }

    public function validationMessages(): array
    {
        return [
            'requiredTypeAmount'       => __('cms.validation_required_type_amount'),
            'duplicateColumns'         => __('cms.validation_duplicate_columns'),
            'wordsWithoutType'         => __('cms.validation_words_without_type'),
            'wordsWithoutTypeMulti'    => __('cms.validation_words_without_type_multi'),
            'columnWithoutWords'       => __('cms.validation_column_without_words'),
            'columnWithoutWordsMulti'  => __('cms.validation_column_without_words_multi'),
            'requiredSubjectWord'      => __('cms.validation_required_subject_word'),
            'requiredSubjectWordMulti' => __('cms.validation_required_subject_word_multi'),
            'requiredWordsPerRow'      => __('cms.validation_required_words_per_row'),
            'requiredWordsPerRowMulti' => __('cms.validation_required_words_per_row_multi'),
        ];
    }

    public function close()
    {
        if (empty($this->newLists)) {
            $this->closeModal();
            return;
        }

        $this->wordLists
            ->whereIn('uuid', $this->newLists)
            ->each(function ($list) {
                if($list->words->isEmpty()) {
                    $list->delete();
                }
            });

        $this->closeModal();
    }
}
