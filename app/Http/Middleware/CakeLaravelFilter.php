<?php

namespace tcCore\Http\Middleware;

use Closure;

class CakeLaravelFilter
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
        if(config('cake_laravel_filter.skip') === true){
            return $next($request);
        } else {
            if($_SERVER['REMOTE_ADDR'] == '127.0.0.1'
                && $_SERVER['SERVER_PORT'] === 81
                && $_SERVER['SERVER_NAME'] == '127.0.0.1') {
                return $next($request);
            } else {
                abort(403,'Access denied');
            }
        }
        return $next($request);
    }
}
