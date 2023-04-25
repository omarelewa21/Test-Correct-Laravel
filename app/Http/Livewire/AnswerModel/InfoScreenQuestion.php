<?php

namespace tcCore\Http\Livewire\AnswerModel;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;

class InfoScreenQuestion extends TCComponent
{
    use WithCloseable, WithGroups;

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
