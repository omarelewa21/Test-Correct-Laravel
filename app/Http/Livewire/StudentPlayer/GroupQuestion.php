<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithStudentPlayerOverview;

class GroupQuestion extends TCComponent
{
    public $question;
    public $answers;
    public $number;
    public $skipMountStudentPlayerOverview;

    use WithAttachments;
    use WithGroups;
    use WithStudentPlayerOverview;
    use WithCloseable;

    public function render()
    {
        return view('livewire.student-player.group-question');
    }
}
