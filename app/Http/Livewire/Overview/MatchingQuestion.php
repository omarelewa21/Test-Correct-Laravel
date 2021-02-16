<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class MatchingQuestion extends Component
{
    use WithAttachments, WithNotepad;

    public $answer;
    public $question;
    public $number;

    public $answers;
    public $answerStruct;

    public function mount()
    {
        $this->question->loadRelated();

        $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);

        if ($this->answers[$this->question->uuid]['answer']) {
            $this->answer = true;
        }
    }

    public function render()
    {
        return view('livewire.overview.matching-question');
    }

}
