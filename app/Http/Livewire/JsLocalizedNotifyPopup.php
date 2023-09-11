<?php

namespace tcCore\Http\Livewire;

use tcCore\Http\Livewire\TCComponent;

class JsLocalizedNotifyPopup extends TCComponent
{
    public function render()
    {
        return view('livewire.js-localized-notify-popup');
    }

    public function getLocalizedMessage($translationKey)
    {
        return __("notify.$translationKey");
    }
}
