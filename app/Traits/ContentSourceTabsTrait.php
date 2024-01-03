<?php

namespace tcCore\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\Http\Livewire\Teacher\WordListsOverview;
use tcCore\Services\ContentSourceFactory;

trait ContentSourceTabsTrait
{
    public $openTab = '';

    public $allowedTabs = [];
    public $schoolLocationInternalContentTabs = [];
    public $schoolLocationExternalContentTabs = [];
    public $canFilterOnAuthorTabs = [
        'school_location',
        'umbrella'
    ];

    public function updatingOpenTab($value)
    {
        $this->abortIfTabNotAllowed($value);

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
        session([self::ACTIVE_TAB_SESSION_KEY => $value]);
    }

    private function initialiseContentSourceTabs()
    {
        $this->openTab = $this->getDefaultOpenTab();

        $this->allowedTabs = ContentSourceHelper::allAllowedForUser(Auth::user(), $this->getContext());

        $this->schoolLocationInternalContentTabs = $this->allowedTabs->filter(
            fn($contentSourceClass, $sourceName) => in_array($sourceName, ['personal', 'school_location'])
        );
        $this->schoolLocationExternalContentTabs = $this->allowedTabs->reject(
            fn($contentSourceClass, $sourceName) => in_array($sourceName, ['personal', 'school_location'])
        );

        $this->rejectExcludedTabs();

        $this->abortIfTabNotAllowed();
    }

    private function abortIfTabNotAllowed($openTab = null): void
    {
        if (!$this->allowedTabs->has($openTab ?? $this->openTab)) {
            abort(404);
        }
    }

    public function isExternalContentTab($tab = null): bool
    {
        return collect($this->schoolLocationExternalContentTabs)->has($tab ?? $this->openTab);
    }

    private function getDefaultOpenTab(): string
    {
        if (session()->has(self::ACTIVE_TAB_SESSION_KEY)) {
            return session()->get(self::ACTIVE_TAB_SESSION_KEY);
        }
        if (Auth::user()->isValidExamCoordinator()) {
            return 'school_location';
        }
        return 'personal';
    }

    private function rejectExcludedTabs()
    {
        if (!isset($this->excludeTabs)) {
            return;
        }

        $this->schoolLocationExternalContentTabs = $this->schoolLocationExternalContentTabs->reject(
            fn($class, $tab) => collect($this->excludeTabs)->contains($tab)
        );
    }

    protected function filterableEducationLevelsBasedOnTab(): Collection
    {
        if (collect($this->schoolLocationInternalContentTabs)->has($this->openTab)) {
            return EducationLevel::filtered(['school_location_id' => Auth::user()->school_location_id])->optionList();
        }

        if ($this->isExternalContentTab()) {
            if ($serviceClass = ContentSourceFactory::makeWithTabExternalOnly($this->openTab)) {
                return EducationLevel::whereIn(
                    'id',
                    $serviceClass->itemBankFiltered(forUser: auth()->user())->select('education_level_id')
                )->optionList();
            }
        }
        return collect();
    }

    public function canFilterOnAuthors(): bool
    {
        return collect($this->canFilterOnAuthorTabs)->contains($this->openTab);
    }

    /**
     * @param string $source
     * @return array|string[]
     */
    protected function getNotAllowedFilterProperties(string $source): array
    {
        $notAllowed = [
            'personal'        => ['base_subject_id', 'author_id', 'shared_sections_author_id'],
            'school_location' => ['base_subject_id', 'shared_sections_author_id'],
            'umbrella'        => ['subject_id', 'author_id'],
            'external'        => ['subject_id', 'author_id', 'shared_sections_author_id'],
        ];

        return $notAllowed[$source];
    }

    protected function getContentSourceFilters(): array
    {
        if ($this->openTab == 'personal') {
            return $this->cleanFilterForSearch($this->filters, 'personal');
        }

        if ($this->openTab == 'umbrella') {
            return $this->getUmbrellaDatasourceFilters();
        }

        $filters = $this->cleanFilterForSearch($this->filters, 'external');
        if (!isset($filters['base_subject_id']) && !Auth::user()->isValidExamCoordinator()) {
            $filters['base_subject_id'] = BaseSubject::currentForAuthUser()->pluck('id')->toArray();
        }
        return $filters;
    }

    protected function getUmbrellaDatasourceFilters(): array
    {
        $filters = $this->cleanFilterForSearch($this->filters, 'umbrella');
        if (!empty($filters['shared_sections_author_id'])) {
            $filters['author_id'] = $filters['shared_sections_author_id'];
        }
        return $filters;
    }

    protected function cleanFilterForSearch(array $filters, string $source): array
    {
        $notAllowed = $this->getNotAllowedFilterProperties($source);

        return collect($filters)->reject(function ($filter, $key) use ($notAllowed) {
            if ($filter instanceof Collection) {
                return $filter->isEmpty() || in_array($key, $notAllowed);
            }
            return empty($filter) || in_array($key, $notAllowed);
        })->toArray();
    }

    private function getContext(): string
    {
        return match (get_class($this)) {
            WordListsOverview::class => 'wordList',
            default                  => 'test',
        };
    }
}
