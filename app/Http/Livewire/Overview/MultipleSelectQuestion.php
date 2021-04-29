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
    public $answered;

    public $answers;

    public $answerStruct;

    public $number;

    public $answerText;
    public $shuffledKeys;


    public function mount()
    {
        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
            $this->answer = 'answered';
        } else {
            $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
                $this->answerStruct[$answers->id] = 0;
            });
        }

        $this->shuffledKeys = array_keys($this->answerStruct);
        if (!$this->question->isCitoQuestion()) {
            if ($this->question->subtype != 'ARQ' && $this->question->subtype != 'TrueFalse') {
                shuffle($this->shuffledKeys);
            }
        }

        $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        });

        $this->answered = $this->answers[$this->question->uuid]['answered'];
    }

    public function render()
    {
        return view('livewire.overview.multiple-select-question');
    }
}
