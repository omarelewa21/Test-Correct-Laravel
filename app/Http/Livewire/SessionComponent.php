<?php

namespace tcCore\Http\Livewire;

use tcCore\Http\Livewire\TCComponent;

class SessionComponent extends TCComponent
{
    protected $allowedSessionKeys = ['isSpellCheckerEnabled'];

    public function render()
    {
        return view('livewire.session-component');
    }

    public function storeToSession($params)
    {
        if(!is_array($params)) return;

        foreach($params as $key => $value) {
            if(in_array($key, $this->allowedSessionKeys)) {
                $this->store($key, $value);
            };
        }
    }
}
