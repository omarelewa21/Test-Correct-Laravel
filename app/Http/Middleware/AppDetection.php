<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
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
        //Only check when it's a Livewire request because the TLC iPad headers aren't send with the initial request,
        //they are added with injected JS.

        if (Livewire::isLivewireRequest()) {
            Session::put('isInBrowser', AppVersionDetector::isInBrowser());
        }

        return $next($request);
    }
}
