<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Livewire\TCComponent;

class ComponentStyleguide extends TCComponent
{
    public function mount()
    {
        // Only allow access to styleguide in local and testing environments
        if(App::isProduction()) {
            abort(404);
        }

    }

    public function render()
    {
        return view('livewire.component-styleguide')
            ->layout('layouts.app-teacher');
    }
}
