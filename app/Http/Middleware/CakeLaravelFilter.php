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

        if(config('cake_laravel_filter.skip') !== true){
            $key = $request->header('CakeLaravelFilterKey',null);

            if($_SERVER['REMOTE_ADDR'] == config('cake_laravel_filter.remote_addr')
                && $_SERVER['SERVER_PORT'] == config('cake_laravel_filter.server_port')
                && $_SERVER['SERVER_NAME'] == config('cake_laravel_filter.server_name')
                && $key == config('cake_laravel_filter.key')) {
                return $next($request);
            } else {
                abort(403,'Access denied');
            }
        }
        return $next($request);
    }
}
