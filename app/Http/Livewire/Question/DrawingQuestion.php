<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Question;

class DrawingQuestion extends Component
{
    use WithAttachments;

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
        return view('livewire.question.drawing-question');
    }
}
