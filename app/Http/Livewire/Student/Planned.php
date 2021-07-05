<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;

class Planned extends Component
{
    use WithPagination, WithStudentTestTakes, WithSorting;

    private $testTakes;

    public function mount()
    {
        $this->sortField = 'test_takes.time_start';
        $this->sortDirection = 'ASC';
    }

    public function render()
    {
        return view('livewire.student.planned', [
            'testTakes' => $this->getSchedueledTestTakesForStudent(null, 6, $this->sortField, $this->sortDirection)
        ]);
    }
}
