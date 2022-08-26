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

    public $traitMountFired = false;

    public $counterer = 0;

    public function updatingOpenTab($value)
    {
        $this->abortIfNotAllowed($value);

        $this->resetPage();
        session(['tests-overview-active-tab' => $value]);
    }

    /**
     * Hydrate method for ContentSourceTrait
     * - Is called before the mount method of the component, but after hydrating the properties.
     * - using the trait-version of mount (mountContentSourceTrait) is not possible,
     * - because it is called after the main mount, which is too late in the lifecycle
     */
    public function hydrateContentSourceTabsTrait()
    {
        if ($this->traitMountFired) {
            return;
        }
        $this->traitMountFired = true;

        $this->initialiseContentSourceTabs();
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
    }

    private function abortIfNotAllowed($openTab = null): void
    {
        if (!$this->allowedTabs->contains($openTab ?? $this->openTab)) {
            abort(404);
        }
    }
}