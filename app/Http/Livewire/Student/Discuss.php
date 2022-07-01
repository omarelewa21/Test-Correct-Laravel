<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Events\NewTestTakeDiscussable;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class Discuss extends Component
{
    use WithPagination, WithStudentTestTakes, WithSorting;

    public $readyToLoad;
    public $paginateBy = 10;

    protected function getListeners()
    {
        return [
            NewTestTakeDiscussable::channelSignature() => '$refresh',
        ];
    }

    public function mount()
    {
        $this->sortField = 'test_takes.time_start';
        $this->sortDirection = 'DESC';
    }

    public function render()
    {
        return view('livewire.student.discuss', [
            'testTakes' => $this->readyToLoad ? $this->getTestTakesToDiscuss($this->sortField, $this->sortDirection) : collect()
        ]);
    }

    public function loadTestTakesToDiscuss()
    {
        $this->readyToLoad = true;
    }

    public function getTestTakesToDiscuss($orderColumn, $orderDirection)
    {
        return TestTake::distinct()->doesntHave('archived_model')
            ->select('test_takes.*', 'tests.name as test_name', 'subjects.name as subject_name', 'test_take_statuses.name as status_name')
            ->leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
            ->leftJoin('test_take_statuses', 'test_take_statuses.id', '=', 'test_takes.test_take_status_id')
            ->where('test_participants.user_id', Auth::id())
            ->whereIn('test_takes.test_take_status_id', [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING])
            ->orderBy($orderColumn, $orderDirection)
            ->paginate($this->paginateBy);
    }
}
