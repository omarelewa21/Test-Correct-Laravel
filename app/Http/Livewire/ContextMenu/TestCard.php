<?php

namespace tcCore\Http\Livewire\ContextMenu;

class TestCard extends ContextMenuComponent
{
    public $testUuid = null;
    public $openTab = null;

    public function setContextValues($uuid, $contextData): bool
    {
        $this->testUuid = $uuid;
        $this->openTab = $contextData['openTab'];

        return true;
    }
}
