<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use Livewire\Component;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Question;

class ArqQuestion extends Component
{
    use WithCloseable;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $uuid;

    public $question;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.test_take_overview_preview.arq-question');
    }
}
