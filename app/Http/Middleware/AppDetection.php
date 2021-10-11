<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use tcCore\Http\Helpers\AppVersionDetector;

class AppDetection
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //Only check when it's a Livewire request because the TLC iPad headers aren't send with the initial request,
        //they are added with injected JS.
//        if (Livewire::isDefinitelyLivewireRequest()) {
//            Session::put('isInBrowser', AppVersionDetector::isInBrowser());
//        }

        //Not checking request headers as they are set too late. App details coming from the TemporaryLogin from Cake.
        $version = Session::get('TLCVersion');
        Session::put('isInBrowser', (!$version || $version == 'x'));

        return $next($request);
    }
}
