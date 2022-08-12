<?php

namespace tcCore\Http\Livewire\TestPrint;

use Livewire\Component;

class GroupQuestion extends Component
{
    public $question;
    public $groupStart = false;

    public $description = '';

    public function render()
    {
        $this->description = $this->question->converted_question_html;

        return view('livewire.test_print.group-question');
    }
}
