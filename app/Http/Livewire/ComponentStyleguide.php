<?php

namespace tcCore\Http\Livewire;

use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\TCComponent;

class ComponentStyleguide extends TCComponent
{
    public int $counter = 0;

    protected $listeners = [
        'count' => 'count'
    ];

    public function render()
    {
        return view('livewire.component-styleguide')
            ->layout('layouts.app-teacher');
    }

    /**
     * Method implemented to test whether the wire:click event works on the components
     */
    public function count()
    {
        $this->counter++;
    }
}
