<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithStudentTestTakes;

class Planned extends Component
{
    use WithPagination, WithStudentTestTakes;

    public function render()
    {
        return view('livewire.student.planned', [
            'testTakes' => $this->getSchedueledTestTakesForStudent(null, 6)
        ]);
    }
}
