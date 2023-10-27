<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;

class StudentPlayerQuestion extends TCComponent
{
    use withCloseable;

    public $question;
    public $answer;
    public $answers;
    public $number;
}