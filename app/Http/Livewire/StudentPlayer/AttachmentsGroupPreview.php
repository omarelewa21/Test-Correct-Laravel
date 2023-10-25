<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithGroups;

class AttachmentsGroupPreview extends TCComponent
{
    public $question;

    use WithPreviewAttachments;
    use WithGroups;

    public function mount($question)
    {
        $this->question = $question;
    }
    public function render()
    {
        return view('livewire.student-player.attachments-group-preview');
    }
}
