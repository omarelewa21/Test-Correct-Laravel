<?php

namespace tcCore\Http\Livewire\Teacher\Cms;

use Illuminate\Support\Collection;
use tcCore\Http\Enums\WordType;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Lib\Models\VersionManager;
use tcCore\RelationQuestion;
use tcCore\Services\CompileWordListService;
use tcCore\Versionable;
use tcCore\Word;
use tcCore\WordList;

class CompileWordListModal extends TCModalComponent
{
    public array $columnHeads;
    public array $wordListUuids = [];

    private Collection $wordLists;
    public array $wordData;

    public ?RelationQuestion $relationQuestion = null;

    public function mount(
        array             $wordData,
        ?RelationQuestion $relationQuestion = null
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

    public function buildWordItem($word, $list)
    {
        return [
            'text'         => $word->text,
            'word_id'      => $word->getKey(),
            'word_list_id' => $list->getKey(),
            'type'         => $word->type
        ];
    }

    private function setWordLists($lists, array $wordData): void
    {
        $wordData = collect($wordData);
        $enabledRows = $wordData->pluck('subject.word_id');

        $this->wordLists = $lists
            ->with('rows')
            ->get()
            ->each(function ($list) use ($enabledRows) {
                $list->wordRows = $list->rows->map(function ($row, $key) use ($list, $enabledRows) {
                    $this->addToEnabledRowsWhenUsed($enabledRows, $row, $list, $key);

                    return collect(WordType::cases())
                        ->mapWithKeys(function ($type) use ($row, $list) {
                            $word = $type === WordType::SUBJECT
                                ? $row
                                : $row->associations->first(fn($word) => $word->type === $type);

                            return $word ? [$type->value => $this->buildWordItem($word, $list)] : [];
                        })
                        ->filter();
                });
                unset($list['rows']);
            });

        $this->wordListUuids = $this->wordLists->pluck('uuid')->toArray();
    }

    private function addToEnabledRowsWhenUsed(Collection $enabledRows, Word $row, WordList $list, int $key): void
    {
        if ($enabledRows->contains($row->getKey())) {
            $list->enabledRows = array_merge($list->enabledRows ?? [], [$key]);
        }
    }

    public function compile($updates)
    {
        /* Compile moet 2 dingen doen:
        1: Voer de verandering aan de lijsten toe
            a.
        2: Update de vraag woorden met de geselecteerde woorden
        */

        $compileService = new CompileWordListService(
            auth()->user(),
            $this->wordLists,
            $this->relationQuestion
        );

        $compileService->categorizeUpdates($updates)
            ->performChanges()
            ->syncWordsToRelationQuestion();

        /* stap 1 */
;





//                                VersionManager::getVersionable($list, auth()->user())->removeWord();

//                                VersionManager::getVersionable($list, auth()->user())->createWord(
//                                    $word['text'],
//                                    $type,
//                                    $subjectWord
//                                );
        /* TODO: stap 2 */

        return true;
    }


}
