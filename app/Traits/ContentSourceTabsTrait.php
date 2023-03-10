<?php

namespace tcCore\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\Test;

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

        $this->allowedTabs = ContentSourceHelper::allAllowedForUser(Auth::user());

        $this->schoolLocationInternalContentTabs = $this->allowedTabs->filter(fn ($contentSourceClass, $sourceName) => in_array($sourceName, ['personal', 'school_location']));
        $this->schoolLocationExternalContentTabs = $this->allowedTabs->reject(fn ($contentSourceClass, $sourceName) => in_array($sourceName, ['personal', 'school_location']));

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
        if (!isset($this->excludeTabs)) return;

        $this->schoolLocationExternalContentTabs = $this->schoolLocationExternalContentTabs->reject(fn($class, $tab) => collect($this->excludeTabs)->contains($tab));

    }

    protected function filterableEducationLevelsBasedOnTab(): Collection
    {
        if (collect($this->schoolLocationInternalContentTabs)->has($this->openTab)) {
            return EducationLevel::filtered(['school_location_id' => Auth::user()->school_location_id])->optionList();
        }

        if ($this->isExternalContentTab()) {
            return EducationLevel::whereIn(
                'id',
                DB::query()->fromSub(
                    Test::when($this->openTab === 'umbrella', fn($query) => $query->sharedSectionsFiltered([], []))
                        ->when($this->openTab === 'national', fn($query) => $query->nationalItemBankFiltered([], []))
                        ->when($this->openTab === 'creathlon', fn($query) => $query->creathlonItemBankFiltered([], []))
                        ->when($this->openTab === 'olympiade', fn($query) => $query->olympiadeItemBankFiltered([], [])),
                    'tests2'
                )->select('education_level_id')
            )->optionList();
        }

        return collect();
    }
}