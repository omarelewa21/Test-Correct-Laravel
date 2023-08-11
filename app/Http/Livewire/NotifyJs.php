<?php

namespace tcCore\Http\Livewire;

use tcCore\Http\Livewire\TCComponent;

class NotifyJs extends TCComponent
{
    public function render()
    {
        return view('livewire.notify-js');
    }

    public function getLocalizedMessage($message)
    {
        return __("notify.$message");
    }
}
