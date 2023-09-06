<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use tcCore\Http\Helpers\BaseHelper;

class LocalOrTesting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(BaseHelper::notProduction()) {
            //BaseHelper::notProduction() is not the same as the inverse of BaseHelper::onProduction() !!!
            return $next($request);
        }

        abort(404);
    }
}
