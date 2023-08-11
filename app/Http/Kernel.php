<?php

namespace tcCore\Http;


use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use tcCore\Http\Middleware\AfterResponse;
use tcCore\Http\Middleware\ApiKey;
use tcCore\Http\Middleware\AppDetection;
use tcCore\Http\Middleware\Authenticate;
use tcCore\Http\Middleware\AuthenticatedAsAccountManager;
use tcCore\Http\Middleware\AuthenticatedAsAdministrator;
use tcCore\Http\Middleware\AuthenticatedAsTeacher;
use tcCore\Http\Middleware\AuthenticatedAsStudent;
use tcCore\Http\Middleware\AuthenticateWithTemporaryLogin;
use tcCore\Http\Middleware\Authorize;
use tcCore\Http\Middleware\AuthorizeBinds;
use tcCore\Http\Middleware\BugsnagRequestId;
use tcCore\Http\Middleware\CakeLaravelFilter;
use tcCore\Http\Middleware\CheckForDeploymentMaintenance;
use tcCore\Http\Middleware\LocalOrTesting;
use tcCore\Http\Middleware\DuplicateLogin;
use tcCore\Http\Middleware\DuplicateLoginLivewire;
use tcCore\Http\Middleware\EncryptCookies;
use tcCore\Http\Middleware\GuestChoice;
use tcCore\Http\Middleware\LocaleMiddleware;
use tcCore\Http\Middleware\Logging;
use tcCore\Http\Middleware\RedirectIfAuthenticated;
use tcCore\Http\Middleware\RequestLogger;
use tcCore\Http\Middleware\SetHeaders;
use tcCore\Http\Middleware\TestTakeForceTakenAwayCheck;
use tcCore\Http\Middleware\TestTakeValidStatus;
use tcCore\Http\Middleware\ValidGeneralTerms;
use tcCore\Http\Middleware\ValidTrialPeriod;
use tcCore\Http\Middleware\TrustProxies;
use tcCore\Http\Middleware\VerifyCsrfToken;

class Kernel extends HttpKernel
{

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
        RequestLogger::class,
        Logging::class,
        BugsnagRequestId::class,
        AfterResponse::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $middlewareAliases = [
        'auth'                  => Authenticate::class,
        'guest'                 => RedirectIfAuthenticated::class,
        'bindings'              => SubstituteBindings::class,
        'api'                   => ApiKey::class,
        'dl'                    => DuplicateLogin::class,
        'dll'                   => DuplicateLoginLivewire::class,
        'authorize'             => Authorize::class,
        'authorizeBinds'        => AuthorizeBinds::class,
        'cakeLaravelFilter'     => CakeLaravelFilter::class,
        'auth.temp'             => AuthenticateWithTemporaryLogin::class,
        'deploymentMaintenance' => CheckForDeploymentMaintenance::class,
        'student'               => AuthenticatedAsStudent::class,
        'forceTaken'            => TestTakeForceTakenAwayCheck::class,
        'guestChoice'           => GuestChoice::class,
        'throttle'              => ThrottleRequests::class,
        'accountManager'        => AuthenticatedAsAccountManager::class,
        'administrator'         => AuthenticatedAsAdministrator::class,
        'testTakeStatus'        => TestTakeValidStatus::class,
        'development'           => LocalOrTesting::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web'     => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            LocaleMiddleware::class,
            CheckForDeploymentMaintenance::class,
            AppDetection::class,
            SetHeaders::class,
        ],
        'teacher' => [
            AuthenticatedAsTeacher::class,
            ValidTrialPeriod::class,
            ValidGeneralTerms::class,
        ]
    ];

}
