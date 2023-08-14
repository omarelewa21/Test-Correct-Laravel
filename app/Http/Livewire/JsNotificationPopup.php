<?php

namespace tcCore\Http\Livewire;

use tcCore\Http\Livewire\TCComponent;

class JsNotificationPopup extends TCComponent
{
    public function render()
    {
        return view('livewire.js-notification-popup');
    }

    public function getLocalizedMessage($message)
    {
        return __("notify.$message");
    }
}
