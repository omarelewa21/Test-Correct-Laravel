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
        'school',
        'umbrella'
    ];

    public $counterer = 0;

    public function updatingOpenTab($value)
    {
        $this->abortIfTabNotAllowed($value);

        $this->resetPage();
        session(['tests-overview-active-tab' => $value]);
    }

    private function initialiseContentSourceTabs()
    {
        $this->allowedTabs = ContentSourceHelper::allAllowedForUser(Auth::user());

        $this->schoolLocationInternalContentTabs = [
            'personal',
            'school',
        ];;

        $this->schoolLocationExternalContentTabs = $this->allowedTabs->reject(function ($tabName) {
            return in_array($tabName, $this->schoolLocationInternalContentTabs);
        })->values();

        $this->openTab = session()->get('tests-overview-active-tab') ?? $this->openTab;

        $this->abortIfTabNotAllowed();
    }

    private function abortIfTabNotAllowed($openTab = null): void
    {
        if (!$this->allowedTabs->contains($openTab ?? $this->openTab)) {
            abort(404);
        }
    }
}