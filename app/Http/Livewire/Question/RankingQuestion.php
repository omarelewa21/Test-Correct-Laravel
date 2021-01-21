<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class RankingQuestion extends Component
{
    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $uuid;
    public $answer;

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;

    }

    public function updatedAnswer($value)
    {
        $this->emitUp('updateAnswer', $this->uuid, $value);
    }

    public $answer = 'Tekst in de editor asdf';

    public function dehydrate() {
        $this->emit('initializeCkEditor');
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();

        return view('livewire.question.ranking-question', compact('question'));
    }
}
