<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;

class MultipleChoiceQuestion extends Component
{
    public $question;

    protected $listeners = ['questionUpdated' => '$refresh'];

    public function render()
    {
        dump('before');
        dump(get_class($this->question) === 'tcCore\Question');
        if (get_class($this->question) === 'tcCore\Question'){
            dump('render');
            $this->question = \tcCore\MultipleChoiceQuestion::find($this->question->getKey());
        }
        return view('livewire.question.multiple-choice-question');
    }
}
