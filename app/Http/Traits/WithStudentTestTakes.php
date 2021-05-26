<?php


namespace tcCore\Http\Traits;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
                ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
                ->select('test_takes.*', 'tests.name as test_name', 'tests.question_count', 'subjects.name as subject_name')
                ->where('test_participants.user_id', Auth::id())
                ->where('test_takes.test_take_status_id', '<=', TestTakeStatus::STATUS_TAKING_TEST)
                ->where('test_takes.time_start', '>=', date('y-m-d'))
                ->orderBy($orderColumn, $orderDirection)
                ->paginate($paginateBy);
        }
        return TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
            ->select('test_takes.*', 'tests.name as test_name', 'subjects.name as subject_name')
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', TestTakeStatus::STATUS_TAKING_TEST)
            ->where('test_takes.time_start', '>=', date('y-m-d'))
            ->orderBy($orderColumn, $orderDirection )
            ->take($amount)
            ->get();
    }

    private function getRatingsForStudent($amount = null, $paginateBy = 0, $orderColumn = 'test_participants.updated_at', $orderDirection = 'desc')
    {
        if ($paginateBy != 0) {
            return TestParticipant::leftJoin('test_takes', 'test_participants.test_take_id', '=', 'test_takes.id')
                ->leftJoin('tests', 'test_takes.test_id', '=', 'tests.id')
                ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
                ->select('test_participants.rating', 'test_takes.time_start', 'test_takes.retake', 'test_takes.user_id', 'tests.name', 'tests.subject_id', 'subjects.name as subject_name')
                ->where('test_participants.user_id', Auth::id())
                ->where('rating', '!=', null)
                ->orderBy($orderColumn, $orderDirection)
                ->paginate($paginateBy);
        }
        return TestParticipant::leftJoin('test_takes', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'test_takes.test_id', '=', 'tests.id')
            ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
            ->select('test_participants.rating', 'test_takes.time_start', 'test_takes.retake', 'test_takes.user_id', 'tests.name', 'tests.subject_id', 'subjects.name as subject_name')
            ->where('test_participants.user_id', Auth::id())
            ->where('rating', '!=', null)
            ->orderBy($orderColumn, $orderDirection)
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

    public function redirectToWaitingRoom($testTakeUuid)
    {
        return redirect(route('student.waiting-room', ['take' => $testTakeUuid]));
    }

    public function getTestTakeStatusTranslationString($testTake)
    {
        $statusName = strtolower($testTake->status_name);

        if (Str::contains($testTake->status_name, ' ')) {
            $statusName = preg_replace($testTake->status_name, '_', ' ');
        }

        return sprintf('general.%s', $statusName);
    }
}