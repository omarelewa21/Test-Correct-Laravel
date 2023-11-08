<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Livewire\StudentPlayer\InfoScreenQuestion as AbstractInfoScreenQuestion;
use tcCore\Http\Traits\WithAttachments;

class InfoScreenQuestion extends AbstractInfoScreenQuestion
{
    use WithGroups;
    use WithAttachments;

    public function render()
    {
        return view('livewire.student-player.overview.info-screen-question');
    }
}
