<?php

namespace tcCore\Http\Livewire\Teacher;

use Auth;
use tcCore\Http\Livewire\OverviewComponent;
use tcCore\TestAuthor;
use tcCore\Traits\ContentSourceTabsTrait;
use tcCore\Word;

class WordsOverview extends OverviewComponent
{

    use ContentSourceTabsTrait;

    protected const PER_PAGE = 18;
    public const ACTIVE_TAB_SESSION_KEY = 'words-overview-active-tab';
    public bool $addable = false;
    public string $view = "page";

    protected array $filterableAttributes = [
        'name'                 => '',
        'education_level_year' => [],
        'education_level_id'   => [],
        'subject_id'           => [],
        'user_id'              => [],
    ];

    public function mount(?bool $addable = false): void
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
        return Word::filtered(filters: $this->getFilters())
            ->whereNull('word_id')
            ->with(['subject:id,name'])
            ->paginate(self::PER_PAGE);
    }

    private function getFilters()
    {
        return collect($this->filters)
            ->filter()
            ->when($this->openTab === 'personal', fn($filters) => $filters->merge(['user_id' => auth()->id()]));
    }
}
