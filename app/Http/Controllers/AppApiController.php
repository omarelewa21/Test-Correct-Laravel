<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\AppFeatureSetting;
use tcCore\Http\Requests\AppApiFeatureFlagRequest;
use tcCore\Http\Requests\AppApiFraudEventRequest;
use tcCore\Http\Requests\AppApiHandInRequest;
use tcCore\Http\Requests\AppApiVersionRequest;
use tcCore\TestParticipant;
use tcCore\TestTakeEvent;
use tcCore\TestTakeEventType;
use tcCore\TestTakeStatus;

class AppApiController extends Controller
{

    public function featureFlags(AppApiFeatureFlagRequest $request)
    {
        $response = AppFeatureSetting::all()->mapWithKeys(function($item,$nr){
                return [$item['title'] => $item['value']];
            });
        return Response::json($response);
    }

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
        $response = ["ok" => true, "handedIn" => false];

        if ($reasonId == null) {
            Bugsnag::notifyError('UnknownTestTakeEventType', 'Reason ' . $reason . ' is not a valid TestTakeEventType.');
            $response['ok'] = false;
            return Response::json($response);
        }


        $isReportedInLastTwoMinutesAndNotConfirmed = $testParticipant->testTake->testTakeEvents()->where('test_take_event_type_id', '=', $reasonId)->whereBetween('created_at', [now()->subMinutes(2), now()])->where('confirmed', '=', 0)->first();
        if ($isReportedInLastTwoMinutesAndNotConfirmed) {
            return Response::json($response);
        }

        $testTakeEvent = new TestTakeEvent();
        $testTakeEvent->setAttribute('test_take_event_type_id', $reasonId);
        $testTakeEvent->setAttribute('test_participant_id', $testParticipant->getKey());
        $testTakeEvent->setAttribute('metadata', json_decode($request->metadata, true));

        // force hand-in test if a VM has been detected, but not if it is an assignment
        if (
            $testTakeEvent->testTakeEventType->reason === "vm" &&
            !$testParticipant->testTake->test->isAssignment()
        ) {
            $testParticipant->setAttribute('test_take_status_id', TestTakeStatus::STATUS_TAKEN)->save();
            $response['handedIn'] = true;
        }

        $testParticipant->testTake->testTakeEvents()->save($testTakeEvent);
        return Response::json($response);
    }

    public function versionInfo(AppApiVersionRequest $request) {
        $res = AppVersionDetector::checkVersionDeadline();
        return Response::json($res);
    }

    public function getCurrentDate() {
        return Response::json(['date' => AppVersionDetector::getHashDate()]);
    }
}
