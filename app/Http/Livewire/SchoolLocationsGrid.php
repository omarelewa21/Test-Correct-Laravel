<?php

namespace tcCore\Http\Livewire;

use Livewire\Component;
use tcCore\SchoolLocation;

class SchoolLocationsGrid extends Component
{
    public $schoolLocations;

    public function mount()
    {
        $this->schoolLocations = SchoolLocation::all();
    }

    public function render()
    {
        return view('livewire.school-locations-grid')->layout('layouts.app-admin');
    }
}
