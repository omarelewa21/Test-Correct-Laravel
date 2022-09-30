<?php namespace tcCore\Http;


use Illuminate\Foundation\Http\Kernel as HttpKernel;
use tcCore\Http\Middleware\AppDetection;
use tcCore\Http\Middleware\AuthenticatedAsAccountManager;
use tcCore\Http\Middleware\AuthenticatedAsAdministrator;
use tcCore\Http\Middleware\AuthenticatedAsTeacher;
use tcCore\Http\Middleware\AuthenticatedAsStudent;
use tcCore\Http\Middleware\AuthenticateWithTemporaryLogin;
use tcCore\Http\Middleware\CheckForDeploymentMaintenance;
use tcCore\Http\Middleware\GuestChoice;
use tcCore\Http\Middleware\LocaleMiddleware;
use tcCore\Http\Middleware\Logging;
use tcCore\Http\Middleware\RequestLogger;
use tcCore\Http\Middleware\TestTakeForceTakenAwayCheck;
use tcCore\Http\Middleware\ValidTrialPeriod;
use tcCore\Http\Middleware\TrustProxies;

class Kernel extends HttpKernel
{

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        // This is an API-only, so no need for sessions and cookies!
        //'Illuminate\Cookie\Middleware\EncryptCookies',
        //'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        //'Illuminate\Session\Middleware\StartSession',
        //'Illuminate\View\Middleware\ShareErrorsFromSession',
        RequestLogger::class,
        Logging::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'                  => 'tcCore\Http\Middleware\Authenticate',
        //'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
        'guest'                 => 'tcCore\Http\Middleware\RedirectIfAuthenticated',
        //'csrf' => 'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken',
        'bindings'              => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'api'                   => 'tcCore\Http\Middleware\ApiKey',
        'dl'                    => 'tcCore\Http\Middleware\DuplicateLogin',
        'dll'                   => 'tcCore\Http\Middleware\DuplicateLoginLivewire',
        'authorize'             => 'tcCore\Http\Middleware\Authorize',
        'authorizeBinds'        => 'tcCore\Http\Middleware\AuthorizeBinds',
        'cakeLaravelFilter'     => 'tcCore\Http\Middleware\CakeLaravelFilter',
        'auth.temp'             => AuthenticateWithTemporaryLogin::class,
        'deploymentMaintenance' => CheckForDeploymentMaintenance::class,
        'student'               => AuthenticatedAsStudent::class,
        'forceTaken'            => TestTakeForceTakenAwayCheck::class,
        'guest_choice'          => GuestChoice::class,
        'throttle'              => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'account_manager'       => AuthenticatedAsAccountManager::class,
        'administrator'         => AuthenticatedAsAdministrator::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web'     => [
            \tcCore\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \tcCore\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            LocaleMiddleware::class,
            CheckForDeploymentMaintenance::class,
            AppDetection::class,
        ],
        'teacher' => [
            AuthenticatedAsTeacher::class,
            ValidTrialPeriod::class,
        ]
    ];

}
