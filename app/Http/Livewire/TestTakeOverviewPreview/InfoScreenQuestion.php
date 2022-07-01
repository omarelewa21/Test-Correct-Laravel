<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

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
        return view('livewire.test_take_overview_preview.info-screen-question');
    }
}
