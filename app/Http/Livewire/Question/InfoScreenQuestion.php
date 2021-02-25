<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class InfoScreenQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable;

    public $question;

    public $number;

    public $answers;

    protected $listeners = ['changeAnswerUpdatedAt'];

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        return view('livewire.question.info-screen-question');
    }

    public function changeAnswerUpdatedAt($uuid)
    {
        Answer::where([
            ['id', $this->answers[$this->question->uuid]['id']]
        ])->update(['json' => null]);
    }

}
