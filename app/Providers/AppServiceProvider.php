<?php namespace tcCore\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\Http\Middleware\AuthenticatedAsStudent;
use tcCore\Http\Middleware\AuthenticatedAsTeacher;
use tcCore\Http\Middleware\DuplicateLogin;
use tcCore\Http\Middleware\DuplicateLoginLivewire;
use tcCore\Http\Middleware\TestTakeForceTakenAwayCheck;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
        if (Str::of(config('app.base_url'))->contains('https')) {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'tcCore\Services\Registrar'
		);

        if ($this->app->isLocal()) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        /** @TODO Uitzoeken of persistent middleware uberhaupt werkt, lijkt namelijk niet te (kunnen) werken. -Roan 24/3/2021 */
        Livewire::addPersistentMiddleware([
            \tcCore\Http\Middleware\Authenticate::class,
            \tcCore\Http\Middleware\RedirectIfAuthenticated::class,
            AuthenticatedAsStudent::class,
            AuthenticatedAsTeacher::class,
            DuplicateLogin::class,
            DuplicateLoginLivewire::class,
            TestTakeForceTakenAwayCheck::class,
        ]);
	}

}
