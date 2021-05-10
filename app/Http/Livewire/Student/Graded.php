<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\User;

class Graded extends Component
{
    use WithPagination, WithStudentTestTakes, WithSorting;

    public $readyToLoad;

    public function mount()
    {
        $this->sortField = 'test_participants.updated_at';
        $this->sortDirection = 'desc';
    }

    public function render()
    {
        return view('livewire.student.graded', [
            'ratings' => $this->readyToLoad ? $this->getRatingsForStudent(null, 10, $this->sortField, $this->sortDirection) : collect()
        ]);
    }

    public function loadRatings()
    {
        $this->readyToLoad = true;
    }

    public function getTeacherNameForRating($userId)
    {
        return User::find($userId)->getFullNameWithAbbreviatedFirstName();
    }
}
