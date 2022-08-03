<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Test;

class TestsOverviewContextMenu extends Component
{
    public $testUuid = null;
    public $openTab = null;

    public function render()
    {
        return view('livewire.teacher.tests-overview-context-menu');
    }

    public function setContextValues($uuid, $tab)
    {
        $this->testUuid = $uuid;
        $this->openTab = $tab;
        return true;
    }

    public function clearContextValues()
    {
        $this->reset();
    }
}
