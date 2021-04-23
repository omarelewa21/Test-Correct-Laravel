<?php


namespace tcCore\Http\Traits;


use Illuminate\Support\Facades\Auth;
use tcCore\TestParticipant;
use tcCore\TestTakeStatus;

trait WithPersonalizedTestTakes
{

    private function fetchTestTakes($paginateBy = 0)
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
        /**
         * Check for activeTab because it only exists (for now) on the Tests component, it does not need redirect then.
         * needs to be refactored into something that reads better
         */
        if (isset($this->activeTab)) {
            $this->waitingroom = true;
            $this->take = $uuid;
            $this->waitingTestTake = $this->getTestTakeDataForWaitingRoom($uuid);
            $this->changeActiveTab($this->waitingroomTab);
            return;
        }

        $this->redirect(route('student.tests', ['waitingroom' => true, 'take' => $uuid]));
    }

    private function getRatings($amount = null)
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