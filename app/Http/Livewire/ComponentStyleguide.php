<?php

namespace tcCore\Http\Livewire;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Gate;
use tcCore\Http\Livewire\TCComponent;

class ComponentStyleguide extends TCComponent
{
    public int $counter = 0;

    protected $listeners = [
        'count' => 'count'
    ];

    public function mount()
    {
        $this->checkAuthorizationOfAuthenticatedUser();
    }

    public function render()
    {
        return view('livewire.component-styleguide')
            ->layout('layouts.base');
    }

    /**
     * Method implemented to test whether the wire:click event works on the components
     */
    public function count()
    {
        $this->counter++;
    }

    /**
     * @return void
     */
    public function checkAuthorizationOfAuthenticatedUser(): void
    {
        Bugsnag::leaveBreadcrumb(sprintf("User: '%s' entered the styleguide", auth()->user()->username ?? 'guest/not logged in'));

        if (Gate::denies('canEnterDevelopmentPage')) {
            Bugsnag::leaveBreadcrumb('WARNING: someone tried to enter a development page (styleguide) but was denied access', Breadcrumb::MANUAL_TYPE);

            abort(404); //don't give the user any information about the existence of this page
        }
    }
}
