<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use tcCore\Http\Helpers\AppVersionDetector;

class AppDetection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Session::put('isInBrowser', AppVersionDetector::isInBrowser());

        return $next($request);
    }
}
