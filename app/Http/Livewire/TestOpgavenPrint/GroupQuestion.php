<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Livewire\Component;

class GroupQuestion extends \tcCore\Http\Livewire\TestPrint\GroupQuestion
{
    public function render()
    {
        $this->description = $this->question->converted_question_html;

        return view('livewire.test_opgaven_print.group-question');
    }
}
