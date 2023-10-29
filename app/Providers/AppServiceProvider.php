<?php

namespace tcCore\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Middleware\AuthenticatedAsStudent;
use tcCore\Http\Middleware\AuthenticatedAsTeacher;
use tcCore\Http\Middleware\DuplicateLogin;
use tcCore\Http\Middleware\DuplicateLoginLivewire;
use tcCore\Http\Middleware\TestTakeForceTakenAwayCheck;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\User;

class AppServiceProvider extends ServiceProvider
{

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

        $this->bootGates();
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

    private function bootGates()
    {
        Gate::define('isAllowedToViewTestTake',fn(User $user, TestTake $testTake, $asInvigilator = true, $asExamCoordinator = true) => $testTake->isAllowedToView($user, $asInvigilator, $asExamCoordinator));

        Gate::define('isAuthorOfTest', fn(User $user, Test $test) => $test->canEdit($user));
        Gate::define('canViewTestDetails', fn(User $user, Test $test) => $test->canViewTestDetails($user));

        Gate::define('useNewTakenTestsOverview', function (User $user) {
            return $user->isA('Teacher') ? $user->schoolLocation->allowNewTakenTestsPage : true;
        });

        Gate::define('canEnterDevelopmentPage', fn(User $user) => auth()->check() && BaseHelper::notProduction());

        Gate::define('canUsePlannedTestPage', fn (User $user) => settings()->canUsePlannedTestPage($user));
        Gate::define('canUseTakenTestPage', fn (User $user) => settings()->canUseTakenTestPage($user));
    }

}
