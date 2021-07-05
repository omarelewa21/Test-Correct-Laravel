<?php

namespace tcCore\Http\Livewire\Preview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;

class InfoScreenQuestion extends Component
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $question;
    public $testId;

    public $number;

    public $answers;

    public $answer = '';

    public function mount()
    {
        if($this->answers[$this->question->uuid]['answered']) {
            $this->answer = 'seen';
        }
    }

    public function render()
    {
        return view('livewire.preview.info-screen-question');
    }

    public function markAsSeen($questionUuid)
    {
        $json = json_encode('seen');
        Answer::updateJson($this->answers[$questionUuid]['id'], $json);
    }

}
