<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Livewire\StudentPlayer\InfoScreenQuestion as AbstractInfoScreenQuestion;

class InfoScreenQuestion extends AbstractInfoScreenQuestion
{
    use WithGroups;

    public function render()
    {
        return view('livewire.student-player.overview.info-screen-question');
    }
}
