<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\AppApiFraudEventRequest;
use tcCore\Http\Requests\AppApiHandInRequest;
use tcCore\TestParticipant;
use tcCore\TestTakeEvent;
use tcCore\TestTakeEventType;

class AppApi extends Controller
{
    public function handIn(AppApiHandInRequest $request, TestParticipant $testParticipant)
    {
        if (!$testParticipant->testTake->test->isAssignment()) {
            $testParticipant->handInTestTake();
        }
        return Response::make(null, 200);
    }

    public function fraudEvent(AppApiFraudEventRequest $request, TestParticipant $testParticipant)
    {
        $reason = $request->reason;
        $reasonId = TestTakeEventType::where('reason', '=', $reason)->value('id');
        if ($reasonId == null) {
            Bugsnag::notifyError('UnknownTestTakeEventType', 'Reason ' . $reason . ' is not a valid TestTakeEventType.');
            return;
        }

        $isReportedInLastTwoMinutesAndNotConfirmed = $testParticipant->testTake->testTakeEvents()->where('test_take_event_type_id', '=', $reasonId)->whereBetween('created_at', [now()->subMinutes(2), now()])->where('confirmed', '=', 0)->first();
        if ($isReportedInLastTwoMinutesAndNotConfirmed) {
            return;
        }

        $testTakeEvent = new TestTakeEvent();
        $testTakeEvent->setAttribute('test_take_event_type_id', $reasonId);
        $testTakeEvent->setAttribute('test_participant_id', $testParticipant->getKey());
        $testTakeEvent->setAttribute('metadata', json_decode($request->metadata, true));
        $testParticipant->testTake->testTakeEvents()->save($testTakeEvent);
    }
}
