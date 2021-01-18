<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class MultipleChoiceQuestion extends Component
{
    public $uuid;

    private $question;

    protected $listeners = ['questionUpdated' => '$refresh'];

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.question.multiple-choice-question', compact('question'));
    }
}
