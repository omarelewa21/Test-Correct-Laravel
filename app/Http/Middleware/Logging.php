<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class Logging
{
    static $URL_PATH_LOGGING = [
        # method = ALL will log every type of method

        # School beheerder
        'School manager' => [
            ["path" => "livewire/message/auth.login", "method" => "ALL"],
            ["path" => "api-c/school_year", "method" => "ALL"],
            ["path" => "api-c/school_year/*", "method" => "ALL"],
            ["path" => "api-c/period*", "method" => "ALL"],
            ["path" => "api-c/school_location/school_class*", "method" => "ALL"],
        ]
    ];

    /**
     * Check if the request should be logged for Loki
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (request()->user() != null) {
            foreach (Logging::$URL_PATH_LOGGING as $role => $routes) {

                if (request()->user()->hasRole($role)) {
                    foreach ($routes as $endpoint) {
                        if (request()->is($endpoint['path']) && ($endpoint['method'] == "ALL" || request()->isMethod($endpoint['method']))) {
                            Log::stack(['loki'])->info("Middleware logging for $role");
                        }
                    }
                }
            }
        }

        return $response;
    }
}
