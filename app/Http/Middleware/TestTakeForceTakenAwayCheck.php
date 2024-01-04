<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Helpers\BaseHelper;
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
        '/livewire/message/student-player.question',
        '/livewire/message/student-player.overview',
        '/livewire/message/student.fraud-detection',
    ];

    public function handle($request, Closure $next)
    {
        if (Str::contains($request->getRequestUri(), $this->urisToValidate)) {
            $testTakeUuid = $this->extractTestTakeUuidFromRequestContent($request);

            if ($testTakeUuid != null && Uuid::isValid($testTakeUuid)) {

                $testTake = TestTake::whereUuid($testTakeUuid)->select('id', 'test_take_status_id')->firstOrFail();

                $testParticipantStatus = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($testTake->id)->value('test_take_status_id');

                if ($testParticipantStatus == TestTakeStatus::STATUS_TAKEN || $testTake->hasStatusTaken()) {
                    if (Livewire::isLivewireRequest()) {
                        return abort(406,'Test taken away');
                    }
                    return response()->make('Test taken away', 406);
                }
            }
        }

        return $next($request);
    }

    private function extractTestTakeUuidFromRequestContent($request)
    {
        return collect(explode('/', BaseHelper::getLivewireOriginalPath($request)))->last();
    }
}
