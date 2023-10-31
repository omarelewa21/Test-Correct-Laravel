<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithGroups;

class AttachmentsGroupPreview extends TCComponent
{
    public $question;
    public $answers;
    public $number;

    use WithPreviewAttachments;
    use WithGroups;


    public function render()
    {
        return view('livewire.student-player.attachments-group-preview');
    }
}
