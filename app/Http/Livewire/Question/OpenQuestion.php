<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class OpenQuestion extends Component
{
    public $uuid;
    protected $listeners = ['questionUpdated' => 'questionUpdated'];
    public $answer = '';

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;
        $this->dispatchBrowserEvent('livewire-refresh');
    }

    public function updatedAnswer($value)
    {
        $this->emitUp('updateAnswer', $this->uuid, $value);
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.question.open-question', compact('question'));
    }
}
