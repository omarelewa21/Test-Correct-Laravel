<?php


namespace tcCore\Http\Traits;


use Illuminate\Support\Facades\Auth;

trait WithPersonalizedTestTakes
{

    private function fetchTestTakes($paginateBy = 0)
    {
        return \tcCore\TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', 3)
            ->where('test_takes.time_start', '>=', date('y-m-d'))
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

    public function startTestTake($uuid)
    {
        $this->redirect(route('student.tests', ['waitingroom' => true,'take' => $uuid]));
    }
}