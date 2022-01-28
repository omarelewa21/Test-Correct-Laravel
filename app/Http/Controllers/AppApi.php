<?php

namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\AppApiHandInRequest;
use tcCore\TestParticipant;

class AppApi extends Controller
{
    public function handIn(AppApiHandInRequest $request, TestParticipant $testParticipant) {
        if (!$testParticipant->testTake->test->isAssignment()) {
            $testParticipant->handInTestTake();
        }
        return Response::make(null, 200);
    }
}
