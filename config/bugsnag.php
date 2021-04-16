<?php

use Illuminate\Support\Facades\Log;

if(!class_exists('AppVersionGetter')) {
    class AppVersionGetter
    {

        private static $releasesPath = "../../.dep/releases";

        public static $defaultFilters = [
            'api_key', 'session_hash', 'main_address', 'main_city', 'main_country', 'main_postal',
            'invoice_address', 'visit_address', 'visit_postal', 'visit_city', 'visit_country',
            'username', 'name_first', 'name_suffix', 'abbreviation', 'password', 'wachtwoord',
            'cakelaravelfilterkey', 'cookie', 'name', 'user'
        ];

        public static function configureAppversion()
        {
            if (!file_exists(self::$releasesPath)) {
                return "";
            }

            $version = self::tailCustom(self::$releasesPath);

            if (!$version) {
                return "";
            }

            return explode(",", $version)[1];
        }

        private static function tailCustom($filepath, $lines = 1, $adaptive = true)
        {

            // Open file
            $f = @fopen($filepath, "rb");
            if ($f === false) return false;

            // Sets buffer size, according to the number of lines to retrieve.
            // This gives a performance boost when reading a few lines from the file.
            if (!$adaptive) $buffer = 4096;
            else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

            // Jump to last character
            fseek($f, -1, SEEK_END);

            // Read it and adjust line number if necessary
            // (Otherwise the result would be wrong if file doesn't end with a blank line)
            if (fread($f, 1) != "\n") $lines -= 1;

            // Start reading
            $output = '';
            $chunk = '';

            // While we would like more
            while (ftell($f) > 0 && $lines >= 0) {

                // Figure out how far back we should jump
                $seek = min(ftell($f), $buffer);

                // Do the jump (backwards, relative to where we are)
                fseek($f, -$seek, SEEK_CUR);

                // Read a chunk and prepend it to our output
                $output = ($chunk = fread($f, $seek)) . $output;

                // Jump back to where we started reading
                fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

                // Decrease our line counter
                $lines -= substr_count($chunk, "\n");

            }

            // While we have too many lines
            // (Because of buffer size we might have read too many)
            while ($lines++ < 0) {

                // Find first newline and remove all text before that
                $output = substr($output, strpos($output, "\n") + 1);

            }

            // Close file and return
            fclose($f);
            return trim($output);

        }
    }
}

$appVersion = AppVersionGetter::configureAppversion();

$filters = empty(env('BUGSNAG_FILTERS')) ? AppVersionGetter::$defaultFilters : array_merge(explode(',', str_replace(' ', '', env('BUGSNAG_FILTERS'))), AppVersionGetter::$defaultFilters);

return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | You can find your API key on your Bugsnag dashboard.
    |
    | This api key points the Bugsnag notifier to the project in your account
    | which should receive your application's uncaught exceptions.
    |
    */

    'api_key' => env('BUGSNAG_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | App Type
    |--------------------------------------------------------------------------
    |
    | Set the type of application executing the current code.
    |
    */

    'app_type' => env('BUGSNAG_APP_TYPE'),

    /*
    |--------------------------------------------------------------------------
    | App Version
    |--------------------------------------------------------------------------
    |
    | Set the version of application executing the current code.
    |
    */

    'app_version' => $appVersion,

    /*
    |--------------------------------------------------------------------------
    | Batch Sending
    |--------------------------------------------------------------------------
    |
    | Set to true to send the errors through to Bugsnag when the PHP process
    | shuts down, in order to prevent your app waiting on HTTP requests.
    |
    | Setting this to false will send an HTTP request straight away for each
    | error.
    |
    */

    'batch_sending' => env('BUGSNAG_BATCH_SENDING'),

    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    |
    | Set what server the Bugsnag notifier should send errors to. By default
    | this is set to 'https://notify.bugsnag.com', but for Bugsnag Enterprise
    | this should be the URL to your Bugsnag instance.
    |
    */

    'endpoint' => env('BUGSNAG_ENDPOINT'),

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Use this if you want to ensure you don't send sensitive data such as
    | passwords, and credit card numbers to our servers. Any keys which
    | contain these strings will be filtered.
    |
    */

    'filters' => $filters,

    /*
    |--------------------------------------------------------------------------
    | Hostname
    |--------------------------------------------------------------------------
    |
    | You can set the hostname of your server to something specific for you to
    | identify it by if needed.
    |
    */

    'hostname' => php_uname('n'),

    /*
    |--------------------------------------------------------------------------
    | Proxy
    |--------------------------------------------------------------------------
    |
    | This is where you can set the proxy settings you'd like us to use when
    | communicating with Bugsnag when reporting errors.
    |
    */

    'proxy' => array_filter([
        'http' => env('HTTP_PROXY'),
        'https' => env('HTTPS_PROXY'),
        'no' => empty(env('NO_PROXY')) ? null : explode(',', str_replace(' ', '', env('NO_PROXY'))),
    ]),

    /*
    |--------------------------------------------------------------------------
    | Project Root
    |--------------------------------------------------------------------------
    |
    | Bugsnag marks stacktrace lines as in-project if they come from files
    | inside your “project root”. You can set this here.
    |
    | If this is not set, we will automatically try to detect it.
    |
    */

    'project_root' => env('BUGSNAG_PROJECT_ROOT'),

    /*
    |--------------------------------------------------------------------------
    | Project Root Regex
    |--------------------------------------------------------------------------
    |
    | Bugsnag marks stacktrace lines as in-project if they come from files
    | inside your “project root”. You can set this here.
    |
    | This option allows you to set it as a regular expression and will take
    | precedence over "project_root" if both are defined.
    |
    */

    'project_root_regex' => env('BUGSNAG_PROJECT_ROOT_REGEX'),

    /*
    |--------------------------------------------------------------------------
    | Strip Path
    |--------------------------------------------------------------------------
    |
    | The strip path is a path to be trimmed from the start of any filepaths in
    | your stacktraces.
    |
    | If this is not set, we will automatically try to detect it.
    |
    */

    'strip_path' => env('BUGSNAG_STRIP_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Strip Path Regex
    |--------------------------------------------------------------------------
    |
    | The strip path is a path to be trimmed from the start of any filepaths in
    | your stacktraces.
    |
    | This option allows you to set it as a regular expression and will take
    | precedence over "strip_path" if both are defined.
    |
    */

    'strip_path_regex' => env('BUGSNAG_STRIP_PATH_REGEX'),

    /*
    |--------------------------------------------------------------------------
    | Query
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like us to automatically record all queries executed
    | as breadcrumbs.
    |
    */

    'query' => env('BUGSNAG_QUERY', true),

    /*
    |--------------------------------------------------------------------------
    | Bindings
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like us to include the query bindings in our query
    | breadcrumbs.
    |
    */

    'bindings' => env('BUGSNAG_QUERY_BINDINGS', false),

    /*
    |--------------------------------------------------------------------------
    | Release Stage
    |--------------------------------------------------------------------------
    |
    | Set the release stage to use when sending notifications to Bugsnag.
    |
    | Leaving this unset will default to using the application environment.
    |
    */

    'release_stage' => env('BUGSNAG_RELEASE_STAGE'),

    /*
    |--------------------------------------------------------------------------
    | Notify Release Stages
    |--------------------------------------------------------------------------
    |
    | Set which release stages should send notifications to Bugsnag.
    |
    */

    'notify_release_stages' => empty(env('BUGSNAG_NOTIFY_RELEASE_STAGES')) ? null : explode(',', str_replace(' ', '', env('BUGSNAG_NOTIFY_RELEASE_STAGES'))),

    /*
    |--------------------------------------------------------------------------
    | Send Code
    |--------------------------------------------------------------------------
    |
    | Bugsnag automatically sends a small snippet of the code that crashed to
    | help you diagnose even faster from within your dashboard. If you don’t
    | want to send this snippet, then set this to false.
    |
    */

    'send_code' => env('BUGSNAG_SEND_CODE', true),

    /*
    |--------------------------------------------------------------------------
    | Callbacks
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like us to enable our default set of notification
    | callbacks. These add things like the cookie information and session
    | details to the error to be sent to Bugsnag.
    |
    | If you'd like to add your own callbacks, you can call the
    | Bugsnag::registerCallback method from the boot method of your app
    | service provider.
    |
    */

    'callbacks' => env('BUGSNAG_CALLBACKS', true),

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    | Enable this if you'd like us to set the current user logged in via
    | Laravel's authentication system.
    |
    | If you'd like to add your own user resolver, you can do this by using
    | callbacks via Bugsnag::registerCallback.
    |
    */

    'user' => env('BUGSNAG_USER', false),

    /*
    |--------------------------------------------------------------------------
    | Logger Notify Level
    |--------------------------------------------------------------------------
    |
    | This sets the level at which a logged message will trigger a notification
    | to Bugsnag.  By default this level will be 'notice'.
    |
    | Must be one of the Psr\Log\LogLevel levels from the Psr specification.
    |
    */

    'logger_notify_level' => env('BUGSNAG_LOGGER_LEVEL'),

    /*
    |--------------------------------------------------------------------------
    | Auto Capture Sessions
    |--------------------------------------------------------------------------
    |
    | Enable this to start tracking sessions and deliver them to Bugsnag.
    |
    */

    'auto_capture_sessions' => env('BUGSNAG_CAPTURE_SESSIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Sessions Endpoint
    |--------------------------------------------------------------------------
    |
    | Sets a url to send tracked sessions to.
    |
    */

    'session_endpoint' => env('BUGSNAG_SESSION_ENDPOINT'),

    /*
    |--------------------------------------------------------------------------
    | Builds Endpoint
    |--------------------------------------------------------------------------
    |
    | Sets a url to send build reports to.
    |
    */

    'build_endpoint' => env('BUGSNAG_BUILD_ENDPOINT'),

];
