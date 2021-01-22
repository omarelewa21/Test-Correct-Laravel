<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class OpenQuestion extends Component
{
    protected $listeners = ['questionUpdated' => 'questionUpdated'];
    public $answer = '';
    public $question;
    public $number;

    public function questionUpdated($uuid, $answer)
    {
        $this->answer = $answer;
        $this->dispatchBrowserEvent('livewire-refresh');
    }

    public function updatedAnswer($value)
    {
        $this->emitUp('updateAnswer', $this->uuid, $value);
    }

    public function render()
    {
        if($this->question->subtype==='short') {
            return view('livewire.question.open-question', compact('question'));
        }

        return view('livewire.question.open-medium-question', compact('question'));
    }
}
