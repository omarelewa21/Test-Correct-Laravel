<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ip
    |--------------------------------------------------------------------------
    |
    | the ip address which is allowed to perfom the request
    |
    */
    'remote_addr' => env('CAKE_LARAVEL_FILTER_SERVER_ADDR','127.0.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Port
    |--------------------------------------------------------------------------
    |
    | the port for which the request is allowed to come from
    |
    */
    'server_port' => env('CAKE_LARAVEL_FILTER_SERVER_PORT',80),

    /*
    |--------------------------------------------------------------------------
    | Server name
    |--------------------------------------------------------------------------
    |
    | the server name which should be active when a request is performed
    |
    */
    'server_name' => env('CAKE_LARAVEL_FILTER_SERVER_NAME','test-correct.test'),

    /*
    |--------------------------------------------------------------------------
    | key
    |--------------------------------------------------------------------------
    |
    | the server name which should be active when a request is performed
    |
    */
    'key' => env('CAKE_LARAVEL_FILTER_KEY',''),

    /*
    |--------------------------------------------------------------------------
    | Skip filter check
    |--------------------------------------------------------------------------
    |
    | the port for which the request is allowed to come from
    |
    */
    'skip' => env('CAKE_LARAVEL_FILTER_SKIP',false),

];