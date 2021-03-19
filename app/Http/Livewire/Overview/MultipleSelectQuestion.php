<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Question;

class MultipleSelectQuestion extends Component
{
    use WithCloseable;

    public $question;

    public $answer = '';

    public $answers;

    public $answerStruct;

    public $number;


    protected $listeners = ['questionUpdated' => 'questionUpdated'];


    public function mount()
    {
        if ($this->answers[$this->question->uuid]['answer']) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
            $this->answer = 'answered';
        } else {
            $this->answerStruct =
                array_fill_keys(
                    array_keys(
                        array_flip(Question::whereUuid($this->question->uuid)
                            ->first()
                            ->multipleChoiceQuestionAnswers->pluck('id')
                            ->toArray()
                        )
                    ), 0
                );
        }
    }

    public function render()
    {
        return view('livewire.overview.multiple-select-question');
    }
}
