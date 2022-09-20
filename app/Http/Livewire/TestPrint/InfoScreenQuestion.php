<?php

namespace tcCore\Http\Livewire\TestPrint;

use Livewire\Component;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class InfoScreenQuestion extends Component
{
    use WithCloseable, WithGroups;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $question;

    public $number;

    public $answers;
    public $attachment_counters;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        return view('livewire.test_print.info-screen-question');
    }
}
