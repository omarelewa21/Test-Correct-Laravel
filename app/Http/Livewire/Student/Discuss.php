<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use tcCore\Events\NewTestTakeDiscussable;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithSorting;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\Scopes\ArchivedScope;
use Illuminate\Support\Facades\DB;

class Discuss extends TCComponent
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
        return TestTake::withoutGlobalScope(ArchivedScope::class)
            ->distinct()
            ->leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
            ->leftJoin('test_take_statuses', 'test_take_statuses.id', '=', 'test_takes.test_take_status_id')
            ->leftJoin('archived_models', function($join){
                $join->on('archived_models.archivable_model_id', '=', 'test_takes.id')
                ->where('archived_models.archivable_model_type', 'tcCore\TestTake');
            })
            ->where('test_participants.user_id', Auth::id())
            ->whereIn('test_participants.test_take_status_id', [ TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING])
            ->whereIn('test_takes.test_take_status_id', [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING])
            ->allowedRelationQuestions(auth()->user())
            ->select('test_takes.*', 'tests.name as test_name', 
                    'subjects.name as subject_name', 'test_take_statuses.name as status_name',
                    DB::raw('CASE WHEN archived_models.user_id = test_takes.user_id THEN 1 ELSE 0 END AS is_archived'))
            ->having('is_archived', 0)
            ->orderBy($orderColumn, $orderDirection)
            ->paginate($this->paginateBy);
    }
}
