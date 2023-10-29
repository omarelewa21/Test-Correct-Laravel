<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestTakeValidStatus
{
    public function handle(Request $request, Closure $next, ...$status)
    {
        $hasValidStatus = TestTake::whereUuid($request->route('testTake'))
            ->whereIn('test_take_status_id', $status)
            ->exists();
        if (!$hasValidStatus) {
            return redirect(
                sprintf(
                    "%s?%s",
                    route('teacher.test-take.open-detail', $request->route('testTake')),
                    $request->getQueryString()
                )
            );
        }

        return $next($request);
    }
}
