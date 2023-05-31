<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Livewire\StudentPlayer\ArqQuestion as AbstractArqQuestion;
use tcCore\Http\Traits\WithGroups;

class ArqQuestion extends AbstractArqQuestion
{
    use WithGroups;

    public function render()
    {
        return view('livewire.student-player.overview.arq-question');
    }
}
