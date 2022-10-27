<?php

namespace tcCore\Http\Livewire\ContextMenu;

use Illuminate\Support\Facades\Auth;

class TestCard extends ContextMenuComponent
{
    public $testUuid = null;
    public $openTab = null;
    public $mode = null;

    public $showNonPublicItems;

    public function setContextValues($uuid, $contextData): bool
    {
        $this->testUuid = $uuid;
        $this->openTab = $contextData['openTab'];
        $this->mode = $contextData['mode'] ?? 'page';
        $this->showNonPublicItems = $this->getShowNonPublicItemsValue();

        return true;
    }

    /**
     * @return bool
     */
    private function getShowNonPublicItemsValue(): bool
    {
        return Auth::user()->isValidExamCoordinator() || $this->openTab !== 'umbrella';
    }
}
