<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;

class QuestionBank extends Component
{
    public $search = '';

    public function mount()
    {

    }

    public function render()
    {
        return view('livewire.teacher.question-bank');
    }
}