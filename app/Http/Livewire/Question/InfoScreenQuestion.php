<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class InfoScreenQuestion extends Component
{
    use WithAttachments, WithNotepad;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $question;

    public $number;

    public $queryString = ['q'];

    public $q;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        return view('livewire.question.info-screen-question');
    }
}
