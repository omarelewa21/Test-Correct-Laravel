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

    public function render()
    {
        return view('livewire.teacher.tests-overview-context-menu');
    }

    public function setUuid($uuid)
    {
        $this->testUuid = $uuid;
        return true;
    }
}
