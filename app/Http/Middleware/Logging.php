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
            ["path" => "api-c/school_year*", "method" => "ALL"],
            ["path" => "api-c/school_class*", "method" => "ALL"],
            ["path" => "api-c/period*", "method" => "ALL"],
            ["path" => "api-c/section*", "method" => "ALL"],
            ["path" => "api-c/shared_sections*", "method" => "ALL"],
            ["path" => "api-c/subject*", "method" => "ALL"],
            ["path" => "api-c/school_location*", "method" => "ALL"],
            ["path" => "api-c/user*", "method" => "ALL"],
            ["path" => "api-c/*import*", "method" => "ALL"],
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
        /** @var Illuminate\Http\Response $response */
        $response = $next($request);

        if (request()->user() != null) {
            foreach (Logging::$URL_PATH_LOGGING as $role => $routes) {

                if (request()->user()->hasRole($role)) {
                    if(
                        collect($routes)->first(function($endpoint){
                            return request()->is($endpoint['path']) && ($endpoint['method'] == "ALL" || request()->isMethod($endpoint['method']));
                        })){
                        if (request()->isMethod('POST')) {
                            try {
                                $extraContext['created_object_id'] = json_decode($response->getContent(), true)['id'];
                            } catch (\Throwable $th) {}
                        }
                        Log::stack(['loki'])->info("Middleware logging for $role", $extraContext);
                    };
                }
            }
        }

        return $response;
    }
}
