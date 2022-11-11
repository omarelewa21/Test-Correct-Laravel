<?php

namespace tcCore\Http\Livewire\Questions\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;
use tcCore\Http\Traits\WithUpdatingHandling;

class InfoScreenQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups, WithUpdatingHandling;

    public $question;

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
        return view('livewire.question.info-screen-question');
    }

    public function markAsSeen($questionUuid)
    {
        $json = json_encode('seen');
        Answer::updateJson($this->answers[$questionUuid]['id'], $json);
    }

}
