<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class MultipleSelectQuestion extends Component
{
    use WithAttachments, WithNotepad;

    public $question;

    public $answer = '';

    public $answers;

    public $answerStruct;

    public $number;

    public function mount()
    {
        $this->answerStruct = collect((array) json_decode($this->answers[$this->question->uuid]['answer']));
    }

    public function updatedAnswer($value)
    {
        $this->answerStruct[$value] === 1 ? $this->answerStruct[$value] = 0 : $this->answerStruct[$value] = 1;

        $json = json_encode($this->answerStruct);

        Answer::where([
            ['id', $this->answers[$this->question->uuid]['id']],
            ['question_id', $this->question->id],
        ])->update(['json' => $json]);

        $this->answer = '';
    }

    public function render()
    {
        return view('livewire.question.multiple-select-question');
    }
}
