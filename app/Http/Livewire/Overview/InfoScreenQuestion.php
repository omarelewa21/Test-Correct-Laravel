<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Question;

class InfoScreenQuestion extends Component
{
    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $question;

    public $number;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        return view('livewire.overview.info-screen-question');
    }
}
