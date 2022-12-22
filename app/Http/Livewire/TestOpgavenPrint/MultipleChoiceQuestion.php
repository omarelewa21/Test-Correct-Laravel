<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class MultipleChoiceQuestion extends \tcCore\Http\Livewire\TestPrint\MultipleChoiceQuestion
{
    public function render()
    {
        if ($this->question->subtype == 'ARQ') {
            return view('livewire.test_opgaven_print.arq-question');
        } elseif ($this->question->subtype == 'TrueFalse') {
            return view('livewire.test_opgaven_print.true-false-question');

        }

        return view('livewire.test_opgaven_print.multiple-choice-question');
    }
}
