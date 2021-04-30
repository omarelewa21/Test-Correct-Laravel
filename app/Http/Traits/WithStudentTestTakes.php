<?php


namespace tcCore\Http\Traits;


use Illuminate\Support\Facades\Auth;
use tcCore\TestParticipant;
use tcCore\TestTakeStatus;

trait WithStudentTestTakes
{

    private function getSchedueledTestTakesForStudent($amount = null, $paginateBy = 0)
    {
        return \tcCore\TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests','tests.id','=','test_takes.test_id')
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', TestTakeStatus::STATUS_TAKING_TEST)
            ->where('test_takes.time_start', '>=', date('y-m-d'))
            ->with('test.subject:id,name')
            ->orderBy('test_takes.time_start', 'ASC')
            ->paginate($paginateBy);
    }

    public function giveInvigilatorNamesFor(\tcCore\TestTake $testTake)
    {
        $invigilators = $testTake->invigilatorUsers->map(function ($invigilator) {
            return $invigilator->getFullNameWithAbbreviatedFirstName();
        });

        return collect($invigilators);
    }

    public function goToWaitingRoom($uuid)
    {
        $this->redirect(route('student.test-takes', ['waitingroom' => true, 'take' => $uuid]));
    }

    private function getRatingsForStudent($amount = null)
    {
        return TestParticipant::leftJoin('test_takes', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'test_takes.test_id', '=', 'tests.id')
            ->select('test_participants.rating', 'test_takes.time_start', 'test_takes.retake', 'tests.name', 'tests.subject_id')
            ->where('test_participants.user_id',Auth::id())
            ->where('rating', '!=', null)
            ->orderBy('test_participants.created_at', 'desc')
            ->take($amount)
            ->get();
    }

    public function getBgColorForRating($rating)
    {
        if($rating > 5.5) {
            return 'bg-cta-primary text-white';
        }
        if ($rating < 5.5) {
            return 'bg-all-red text-white';
        }
        return 'bg-orange base';
    }

}