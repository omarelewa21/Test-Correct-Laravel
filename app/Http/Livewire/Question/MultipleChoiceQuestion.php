<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class MultipleChoiceQuestion extends Component
{
    public $uuid;

    private $question;

    public $answer = '';

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;
    }

    public function updatedAnswer($value)
    {
        $this->emitUp('updateAnswer', $this->uuid, $value);
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.question.multiple-choice-question', compact('question'));
    }
}
