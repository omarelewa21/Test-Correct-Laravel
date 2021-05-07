<?php


namespace tcCore\Http\Traits;


use Illuminate\Support\Facades\Auth;
use tcCore\TestParticipant;
use tcCore\TestTakeStatus;
use tcCore\TestTake;

trait WithStudentTestTakes
{

    private function getSchedueledTestTakesForStudent($amount = null, $paginateBy = 0, $orderColumn = 'test_takes.time_start', $orderDirection = 'ASC')
    {
        if ($paginateBy != 0) {
            return TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
                ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
                ->where('test_participants.user_id', Auth::id())
                ->where('test_takes.test_take_status_id', '<=', TestTakeStatus::STATUS_TAKING_TEST)
                ->where('test_takes.time_start', '>=', date('y-m-d'))
                ->with('test.subject:id,name')
                ->orderBy($orderColumn, $orderDirection)
                ->paginate($paginateBy);
        }
        return TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', TestTakeStatus::STATUS_TAKING_TEST)
            ->where('test_takes.time_start', '>=', date('y-m-d'))
            ->with('test.subject:id,name')
            ->orderBy($orderColumn, $orderDirection)
            ->take($amount)
            ->get();
    }

    private function getRatingsForStudent($amount = null, $paginateBy = 0)
    {
        if ($paginateBy != 0) {
            return TestParticipant::leftJoin('test_takes', 'test_participants.test_take_id', '=', 'test_takes.id')
                ->leftJoin('tests', 'test_takes.test_id', '=', 'tests.id')
                ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
                ->select('test_participants.rating', 'test_takes.time_start', 'test_takes.retake', 'tests.name', 'tests.subject_id', 'subjects.name as subject_name')
                ->where('test_participants.user_id', Auth::id())
                ->where('rating', '!=', null)
                ->orderBy('test_participants.created_at', 'desc')
                ->paginate($paginateBy);
        }
        return TestParticipant::leftJoin('test_takes', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'test_takes.test_id', '=', 'tests.id')
            ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
            ->select('test_participants.rating', 'test_takes.time_start', 'test_takes.retake', 'tests.name', 'tests.subject_id', 'subjects.name as subject_name')
            ->where('test_participants.user_id', Auth::id())
            ->where('rating', '!=', null)
            ->orderBy('test_participants.created_at', 'desc')
            ->take($amount)
            ->get();
    }

    public function getBgColorForRating($rating)
    {
        if ($rating > 5.5) {
            return 'bg-cta-primary text-white';
        }
        if ($rating < 5.5) {
            return 'bg-all-red text-white';
        }
        return 'bg-orange base';
    }

    public function redirectToWaitingRoom($testTakeId)
    {
        return redirect(route('student.waiting-room', ['take' => TestTake::whereId($testTakeId)->value('uuid')]));
    }

}