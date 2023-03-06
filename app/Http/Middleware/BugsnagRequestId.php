<?php

namespace tcCore\Http\Middleware;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class BugsnagRequestId
{
    /**
     * Generate random ID to show on error.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // random string to display when an error is fired
        $error_id = Str::random(16);

        $request->request->set("error_id", $error_id);

        // the $error_id can be displayed to the user and will be recorded in Bugsnag on exception:
        Bugsnag::setMetaData([
            'session' => [
                'error_id' => $error_id
            ]
        ]);

        return $next($request);
    }
}
