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
    const PAGE_NUMBER_KEY = 'student-page-number';

    public $readyToLoad;
    protected $queryString = [
    'page' => ['except' => '', 'as' => 'page']
    ];
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
        $this->setPageNumber();
    }

    private function setPageNumber()
    {
        $page = request()->get('page');
        $this->page = $page;
        $this->gotoPage($page);
    }

    public function updatedPage()
    {
        session([self::PAGE_NUMBER_KEY => $this->page]);
    }
    
    public function render()
    {
        return view('livewire.student.graded', [
            'testTakes' => $this->readyToLoad ? $this->getRatingsForStudent(null, 10, $this->sortField, $this->sortDirection) : collect(),
            'page' =>$this->page
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
