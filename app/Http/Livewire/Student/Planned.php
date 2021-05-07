<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithStudentTestTakes;

class Planned extends Component
{
    use WithPagination, WithStudentTestTakes;

    private $testTakes;
    public $sortField = 'test_takes.time_start';
    public $sortDirection = 'asc';

    public function render()
    {
        return view('livewire.student.planned', [
            'testTakes' => $this->getSchedueledTestTakesForStudent(null, 6, $this->sortField, $this->sortDirection)
        ]);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }
}
