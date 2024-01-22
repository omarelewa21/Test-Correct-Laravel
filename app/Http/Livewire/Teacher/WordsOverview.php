<?php

namespace tcCore\Http\Livewire\Teacher;

use Auth;
use Illuminate\Support\Arr;
use tcCore\Http\Livewire\OverviewComponent;
use tcCore\Http\Traits\WithVersionableCmsHandling;
use tcCore\Services\ContentSourceFactory;
use tcCore\TestAuthor;
use tcCore\Traits\ContentSourceTabsTrait;
use tcCore\Word;
use tcCore\WordListWord;

class WordsOverview extends OverviewComponent
{
    use ContentSourceTabsTrait;
    use WithVersionableCmsHandling;

    protected const PER_PAGE = 18;
    public const ACTIVE_TAB_SESSION_KEY = 'words-overview-active-tab';
    public string $view = "page";
    protected array $updateListenerKeys = ['used.lists'];

    protected array $filterableAttributes = [
        'name'                      => '',
        'education_level_year'      => [],
        'education_level_id'        => [],
        'subject_id'                => [],
        'user_id'                   => [],
        'shared_sections_author_id' => [],
        'base_subject_id'           => [],
    ];

    protected $listeners = ['newListAdded'];

    public function mount(): void
    {
        $this->initialiseContentSourceTabs();

        parent::mount();
    }

    public function render()
    {
        return view('livewire.teacher.words-overview')
//            ->layout('layouts.base')
            ->with(['results' => $this->getOverviewResults()]);
    }

    public function getUsersProperty()
    {
        return TestAuthor::schoolLocationAuthorUsers(Auth::user())
            ->get()
            ->map(function ($user) {
                return ['value' => $user->id, 'label' => $user->nameFull];
            })
            ->values()
            ->toArray();
    }

    private function getOverviewResults()
    {
        return ContentSourceFactory::makeWithTab($this->openTab)
            ->wordFiltered(
                forUser: auth()->user(),
                filters: $this->getContentSourceFilters(),
            )
            ->with(['subject:id,name'])
            ->paginate(self::PER_PAGE);
    }

    public function newListAdded(int $id): void
    {
        $newWordIds = WordListWord::whereWordListId($id)->pluck('word_id');
        array_push($this->used, ...$newWordIds);
    }

    protected function handleUpdatedProperties(array $updates): void
    {
        if (!Arr::hasAny($updates, $this->updateListenerKeys)) {
            return;
        }

        if (isset($updates['used.words'])) {
            $this->used = $updates['used.words'];
        }
    }
}
