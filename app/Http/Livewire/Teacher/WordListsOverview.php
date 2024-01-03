<?php

namespace tcCore\Http\Livewire\Teacher;

use Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use tcCore\Http\Livewire\OverviewComponent;
use tcCore\Http\Traits\WithVersionableCmsHandling;
use tcCore\Services\ContentSourceFactory;
use tcCore\TestAuthor;
use tcCore\Traits\ContentSourceTabsTrait;

class WordListsOverview extends OverviewComponent
{
    use ContentSourceTabsTrait;
    use WithVersionableCmsHandling;

    protected const PER_PAGE = 18;
    public const ACTIVE_TAB_SESSION_KEY = 'word-lists-overview-active-tab';
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

    public function mount(): void
    {
        $this->initialiseContentSourceTabs();

        parent::mount();
    }

    public function render(): View
    {
        return view('livewire.teacher.word-lists-overview')
            ->layout('layouts.base')
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
            ->wordListFiltered(
                forUser: auth()->user(),
                filters: $this->getContentSourceFilters(),
            )
            ->with(['subject:id,name', 'words'])
            ->paginate(self::PER_PAGE);
    }

    protected function handleUpdatedProperties(array $updates): void
    {
        if (!Arr::hasAny($updates, $this->updateListenerKeys)) {
            return;
        }

        if (isset($updates['used.lists'])) {
            $this->used = $updates['used.lists'];
        }
    }
}
