<?php

namespace tcCore\Http\Middleware;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Closure;


class RequestLogger
{
    protected $tresholdInMilliSeconds = 5000;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {

        if ($request->path() == 'fpm-status' || $request->path() == 'nginx_status') {
            return true;
        }

        $end = microtime(true) * 1000;

        $duration = $end - (LARAVEL_START * 1000);

        if($duration > $this->tresholdInMilliSeconds){
            Bugsnag::notifyError('Request too slow','The current request was too slow '.$duration);
        }
    }

}
