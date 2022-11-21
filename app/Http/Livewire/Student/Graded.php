<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Events\NewTestTakeGraded;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\User;

class Graded extends Component
{
    use WithPagination, WithStudentTestTakes, WithSorting;

    public $readyToLoad;
    
    protected function getListeners()
    {
        return [
            NewTestTakeGraded::channelSignature() => '$refresh'
        ];
    }

    public function mount()
    {
        $this->sortField = 'test_takes.updated_at';
        $this->sortDirection = 'desc';
    }
    
    public function render()
    {
        return view('livewire.student.graded', [
            'testTakes' => $this->readyToLoad ? $this->getRatingsForStudent(null, 10, $this->sortField, $this->sortDirection) : collect()
        ]);
    }

    public function loadRatings()
    {
        $this->readyToLoad = true;
    }

    public function getTeacherNameForRating($userId)
    {
        return User::withTrashed()->find($userId)->getFullNameWithAbbreviatedFirstName();
    }

    public function testTakeReviewable($testTake)
    {
        return $testTake->show_results != null && $testTake->show_results->gt(now()) && ($testTake->testParticipants->first()->rating || $testTake->testParticipants->first()->retake_rating);
    }
}
