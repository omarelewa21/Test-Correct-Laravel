<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class Review extends Component
{
    use WithPagination, WithStudentTestTakes, WithSorting;

    public $readyToLoad;
    public $paginateBy = 10;

    public function mount()
    {
        $this->sortField = 'test_takes.time_start';
        $this->sortDirection = 'DESC';
    }

    public function render()
    {
        return view('livewire.student.review', [
            'testTakes' => $this->readyToLoad ? $this->getTestTakesToReview($this->sortField, $this->sortDirection) : collect()
        ]);
    }

    public function loadTestTakesToReview()
    {
        $this->readyToLoad = true;
    }

    public function getTestTakesToReview($orderColumn, $orderDirection)
    {
        return TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
            ->select('test_takes.*', 'tests.name as test_name', 'subjects.name as subject_name', 'test_participants.invigilator_note as participant_invigilator_note')
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.show_results', '>=', Carbon::now())
            ->orderBy($orderColumn, $orderDirection)
            ->paginate($this->paginateBy);
    }
}
