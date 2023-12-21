<?php

namespace tcCore\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\NationalItemBankService;
use tcCore\Services\ContentSource\OlympiadeService;
use tcCore\Services\ContentSource\PersonalService;
use tcCore\Services\ContentSource\SchoolLocationService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\Services\ContentSource\UmbrellaOrganizationService;
use tcCore\Services\ContentSourceFactory;
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

        $this->schoolLocationInternalContentTabs = $this->allowedTabs->filter(fn($contentSourceClass, $sourceName) => in_array($sourceName, ['personal', 'school_location']));
        $this->schoolLocationExternalContentTabs = $this->allowedTabs->reject(fn($contentSourceClass, $sourceName) => in_array($sourceName, ['personal', 'school_location']));
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
            if ($serviceClass = ContentSourceFactory::makeWithTabExternalOnly($this->openTab)) {
                return EducationLevel::whereIn(
                    'id',
                    $serviceClass->itemBankFiltered(filters:[], sorting:[], forUser: auth()->user())->select('education_level_id')
                )->optionList();
            }
        }
        return collect();
    }

    public function canFilterOnAuthors(): bool
    {
        return collect($this->canFilterOnAuthorTabs)->contains($this->openTab);
    }

}
