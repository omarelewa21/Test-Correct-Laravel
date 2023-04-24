<?php

namespace tcCore\Http\Middleware;

use Closure;

class AfterResponse
{
    public static array $performAction = [];

    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        if (empty(self::$performAction)) {
            return;
        }

        collect(self::$performAction)->each(fn($callback) => $callback());
    }
}
