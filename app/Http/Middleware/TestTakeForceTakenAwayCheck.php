<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestTakeForceTakenAwayCheck
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */

    private $urisToValidate = [
        '/livewire/message/question',
        '/livewire/message/overview',
        '/livewire/message/student.fraud-detection',
    ];

    public function handle($request, Closure $next)
    {
        if (Str::contains($request->getRequestUri(), $this->urisToValidate)) {
            $testTakeUuid = $this->extractTestTakeUuidFromRequestContent($request);

            if ($testTakeUuid != null && Uuid::isValid($testTakeUuid)) {

                $testTake = TestTake::whereUuid($testTakeUuid)->select('id', 'test_take_status_id')->firstOrFail();

                $testParticipantStatus = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($testTake->id)->value('test_take_status_id');

                if ($testParticipantStatus == TestTakeStatus::STATUS_TAKEN || $testTake->test_take_status_id == TestTakeStatus::STATUS_TAKEN) {
                    return response()->make('Test taken away', 406);
                }
            }
        }

        return $next($request);
    }

    private function extractTestTakeUuidFromRequestContent($request)
    {
        return collect(explode('/', json_decode($request->getContent())->fingerprint->path))->last();
    }
}
