<?php

namespace tcCore\Http\Livewire;

use tcCore\Http\Livewire\TCComponent;
use tcCore\UserFeatureSetting;

class SessionComponent extends TCComponent
{
    protected $allowedKeys = ['isSpellCheckerEnabled'];

    public function render()
    {
        return view('livewire.session-component');
    }

    public function storeToSession($params)
    {
        if(!is_array($params)) return;

        foreach($params as $key => $value) {
            if(!in_array($key, $this->allowedKeys)) continue;

            match ($key) {
                'isSpellCheckerEnabled' => $this->storeIsSpellCheckerEnabled($value),
            };
        }
    }

    public function storeIsSpellCheckerEnabled($value)
    {
        if(!auth()->user()->isA('teacher') || !is_bool($value)) return;

        UserFeatureSetting::setSetting(auth()->user(), 'spellchecker_enabled', $value);
    }
}
