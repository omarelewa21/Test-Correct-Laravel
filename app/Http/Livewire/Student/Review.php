<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use tcCore\Events\NewTestTakeReviewable;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class Review extends TCComponent
{
    use WithPagination;
    use WithSorting;
    use WithStudentTestTakes;

    public $readyToLoad;
    public $paginateBy = 10;

    protected function getListeners()
    {
        return [
            NewTestTakeReviewable::channelSignature() => '$refresh'
        ];
    }

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

    public function loadTestTakesToReview(): void
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
            ->whereIn('test_participants.test_take_status_id',  [ TestTakeStatus::STATUS_DISCUSSED, TestTakeStatus::STATUS_RATED])
            ->where(function ($query) {
                $query->where('test_takes.test_take_status_id', TestTakeStatus::STATUS_DISCUSSED)
                    ->orWhere('test_takes.test_take_status_id', TestTakeStatus::STATUS_RATED);
            })
            ->where('test_takes.show_results', '>=', Carbon::now())
            ->orderBy($orderColumn, $orderDirection)
            ->paginate($this->paginateBy);
    }
}
