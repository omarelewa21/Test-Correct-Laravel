<?php

namespace tcCore\Http\Livewire\ContextMenu;

use Illuminate\Support\Facades\Auth;
use tcCore\TestTake;

class TestTakeCard extends ContextMenuComponent
{
    public $uuid = null;

    public function setContextValues($uuid, $contextData): bool
    {
        $this->uuid = $uuid;

        return true;
    }

    public function openTestTakeDetail()
    {
        return TestTake::redirectToDetailPage($this->uuid);
    }

    public function archive()
    {
        TestTake::whereUuid($this->uuid)->first()->archiveForUser(Auth::user());

        $this->dispatchBrowserEvent('notify', ['message' => 'gearchiveerd']);
    }

    public function skipDiscussing()
    {

    }
}
