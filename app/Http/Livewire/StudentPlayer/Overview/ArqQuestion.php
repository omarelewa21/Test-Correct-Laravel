<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Livewire\StudentPlayer\ArqQuestion as AbstractArqQuestion;
use tcCore\Http\Traits\WithGroups;

class ArqQuestion extends AbstractArqQuestion
{
    use WithGroups;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];
}
