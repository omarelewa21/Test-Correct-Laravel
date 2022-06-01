<?php

namespace tcCore\Http\Livewire\AnswerModel;

use Livewire\Component;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Question;

class InfoScreenQuestion extends Component
{
    use WithCloseable;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $question;

    public $number;

    public $answers;


    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        return view('livewire.answer_model.info-screen-question');
    }
}
