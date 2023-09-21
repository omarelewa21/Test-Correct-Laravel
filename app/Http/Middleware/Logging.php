<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Logging
{
    static $URL_PATH_LOGGING = [
        # method = ALL will log every type of method
        # Every role can have either a blacklist or whitelist, but not both
        'Administrator' => [
            'blacklist' => [
                ["path" => "api-c/message", "method" => "GET"],
            ]
        ],
        'Support' => [
            'blacklist' => [
                ["path" => "api-c/message", "method" => "GET"],
            ]
        ],
        'Tech administrator' => [
            'blacklist' => [
                ["path" => "api-c/message", "method" => "GET"],
            ]
        ],
        'School management' => [
            'blacklist' => [
                ["path" => "api-c/message", "method" => "GET"],
            ]
        ],
        'School manager' => [
            'blacklist' => [
                ["path" => "api-c/message", "method" => "GET"],
            ]
        ],
        'Account manager' => [
            'blacklist' => [
                ["path" => "api-c/message", "method" => "GET"],
            ]
        ],
        'Teacher' => [
            'blacklist' => [
                ["path" => "api-c/message", "method" => "GET"],
            ]
        ],
        'Student' => [
            'whitelist' => [
                ["message" => "authenticated", "path" => "livewire/message/auth.login", "method" => "ALL"],
            ]
        ]
    ];

    private function log($response, $role, $message = "")
    {
        $extraContext = [];
        if (request()->isMethod('POST')) {
            try {
                $extraContext['created_object_id'] = json_decode($response->getContent(), true)['id'];
            } catch (\Throwable $th) {
            }
        }
        if ($message != "" && !is_null($message)) {
            $message = ": " . $message;
        }

        Log::stack(['loki'])->info("Middleware logging for $role" . $message, $extraContext);
    }

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

        // Usefull for determining paths to log:
        // Log::debug([request()->path(), request()->method(), request()->header('cakeUrlPath', null)]);

        if (request()->user() != null) {
            foreach (Logging::$URL_PATH_LOGGING as $role => $_) {

                if (request()->user()->hasRole($role)) {
                    $this->role = $role;

                    // inline function determining which routes matches given the routes
                    // the cakepath is optional and may be ignored. It will be ignored if it is not a Cake request
                    $routeMatchesFunc = function ($routes) {
                        return collect($routes)->filter(function ($endpoint) {
                            $result =  (request()->is($endpoint['path']) && ($endpoint['method'] == "ALL" || request()->isMethod($endpoint['method'])));
                            if (request()->ip() == config('cake_laravel_filter.remote_addr') && !is_null(request()->header('cakeUrlPath', null)) && array_key_exists('cakepath', $endpoint) && $result) {
                                $cakeUrlPath = request()->header('cakeUrlPath', null);
                                return (Str::is($endpoint['cakepath'], $cakeUrlPath));
                            }
                            return $result;
                        });
                    };

                    // log with message if there is a match in the whitelist
                    if (array_key_exists('whitelist', Logging::$URL_PATH_LOGGING[$role])) {
                        $result = $routeMatchesFunc(Logging::$URL_PATH_LOGGING[$role]['whitelist']);
                        if ($result->isNotEmpty()) {
                            $endpoint = $result->first();
                            $this->log($response, $role, $endpoint['message']);
                        }

                        // log a message if there is no match (blacklist)
                    } else if (array_key_exists('blacklist', Logging::$URL_PATH_LOGGING[$role])) {
                        $result = $routeMatchesFunc(Logging::$URL_PATH_LOGGING[$role]['blacklist']);
                        if ($result->isEmpty()) {
                            $this->log($response, $role);
                        }
                    }
                }
            }
        }

        return $response;
    }
}
