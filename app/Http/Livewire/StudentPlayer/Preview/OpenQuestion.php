<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Livewire\StudentPlayer\OpenQuestion as AbstractOpenQuestion;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;

class OpenQuestion extends AbstractOpenQuestion
{
    use withCloseable;
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $answer = '';
    public $question;
    public $testId;
    public $number;
    public $answers;
    public $editorId;

    public function mount()
    {
        $this->editorId = 'editor_' . $this->question->id;
    }

    public function updatedAnswer($value) {}

    public function render()
    {
        return view('livewire.student-player.preview.open-question');
    }
}
