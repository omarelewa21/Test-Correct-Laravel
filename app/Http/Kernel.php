<?php namespace tcCore\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

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
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		//'auth' => 'tcCore\Http\Middleware\Authenticate',
		//'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		//'guest' => 'tcCore\Http\Middleware\RedirectIfAuthenticated',
		//'csrf' => 'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken',
		'api' => 'tcCore\Http\Middleware\ApiKey',
		'dl' => 'tcCore\Http\Middleware\DuplicateLogin',
		'authorize' => 'tcCore\Http\Middleware\Authorize',
		'authorizeBinds' => 'tcCore\Http\Middleware\AuthorizeBinds'
	];

}
