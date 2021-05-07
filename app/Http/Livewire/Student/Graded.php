<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithStudentTestTakes;

class Graded extends Component
{
    use WithPagination, WithStudentTestTakes;

    public $readyToLoad;

    public function render()
    {
        return view('livewire.student.graded', [
            'ratings' => $this->readyToLoad ? $this->getRatingsForStudent(null, 10) : collect()
        ]);
    }

    public function loadRatings()
    {
        $this->readyToLoad = true;
    }
}
