<?php

namespace tcCore\Http\Livewire\ContextMenu;

use Illuminate\Support\Facades\Auth;
use tcCore\Test;

class TestCard extends ContextMenuComponent
{
    public $testUuid = null;
    public $openTab = null;
    public $mode = null;

    public $showNonPublicItems;
    public $publishable;

    public function setContextValues($uuid, $contextData): bool
    {
        $this->testUuid = $uuid;
        $this->openTab = $contextData['openTab'];
        $this->mode = $contextData['mode'] ?? 'page';
        $this->showNonPublicItems = $this->getShowNonPublicItemsValue();
        $this->publishable = $this->getPublishableValue();

        return true;
    }

    /**
     * @return bool
     */
    private function getShowNonPublicItemsValue(): bool
    {
        return Auth::user()->isValidExamCoordinator() || $this->openTab !== 'umbrella';
    }

    private function getPublishableValue(): bool
    {
        return !!Test::whereUuid($this->testUuid)->value('draft');
    }
}
