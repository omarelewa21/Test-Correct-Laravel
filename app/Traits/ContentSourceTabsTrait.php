<?php

namespace tcCore\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\ContentSourceHelper;

trait ContentSourceTabsTrait
{
    public $openTab = 'personal';

    public $allowedTabs = [];
    public $schoolLocationInternalContentTabs = [];
    public $schoolLocationExternalContentTabs = [];
    public $canFilterOnAuthorTabs = [
        'school_location',
        'umbrella'
    ];

    public $counterer = 0;

    public function updatingOpenTab($value)
    {
        $this->abortIfTabNotAllowed($value);

        if(method_exists($this, 'resetPage')){
            $this->resetPage();
        }
        session([self::ACTIVE_TAB_SESSION_KEY => $value]);
    }

    private function initialiseContentSourceTabs()
    {
        $this->allowedTabs = ContentSourceHelper::allAllowedForUser(Auth::user());

        $this->schoolLocationInternalContentTabs = [
            'personal',
            'school_location',
        ];;

        $this->schoolLocationExternalContentTabs = $this->allowedTabs->reject(function ($tabName) {
            return in_array($tabName, $this->schoolLocationInternalContentTabs);
        })->values();
        $this->openTab = session()->get(self::ACTIVE_TAB_SESSION_KEY) ?? $this->openTab;

        $this->abortIfTabNotAllowed();
    }

    private function abortIfTabNotAllowed($openTab = null): void
    {
        if (!$this->allowedTabs->contains($openTab ?? $this->openTab)) {
            abort(404);
        }
    }

    public function isExternalContentTab($tab = null): bool
    {
        return collect($this->schoolLocationExternalContentTabs)->contains($tab ?? $this->openTab);
    }

}