<?php

namespace tcCore\Http\Middleware;

use tcCore\Log;
use Closure;
use Illuminate\Support\Facades\Auth;

class RequestLogger
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!defined('LARAVEL_START')){
            define('LARAVEL_START',microtime(true));
        }
        return $next($request);
    }

    public function terminate($request, $response){
        $end = microtime(true) * 1000;
        $ip = $request->ip();

        if (array_key_exists("HTTP_CF_CONNECTING_IP", $_SERVER)) {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        if(!defined('LARAVEL_START')){
            define('LARAVEL_START',microtime(true));
        }
        $record = [
            'uri' => $request->url(),
            'uri_full' => $request->fullUrl(),
            'method' => $request->method(),
            'request' => json_encode($request->except(['password'])),
            'response' => $response->getContent(),
            'headers'   => json_encode($request->headers->all()),
            'code' => $response->getStatusCode(),
            'ip' => $ip,
            'duration' => $end - (LARAVEL_START * 1000),
            'created_at' => date("Y-m-d H:i:s"),
            'user_id' => Auth::user() ? Auth::user()->id : null,
            'user_agent' => $request->header('User-Agent'),
            'success' => $response->getStatusCode() === 200
        ];
        Log::create($record);
    }
}
