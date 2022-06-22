<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;

class TestsOverviewContextMenu extends Component
{
    public $displayMenu = false;


    public function showMenu($testUuid) {
        $this->test = Test::whereUuid($testUuid)->first();
    }

    public function render()
    {
        return view('livewire.teacher.tests-overview-context-menu');
    }
}
