<?php

namespace tcCore\Http\Livewire\TestPrint;

use tcCore\Http\Livewire\TCComponent;

class GroupQuestion extends TCComponent
{
    public $question;
    public $groupStart = false;

    public $description = '';
    public $attachment_counters;

    public function render()
    {
        $this->description = $this->question->converted_question_html;

        return view('livewire.test_print.group-question');
    }
}
