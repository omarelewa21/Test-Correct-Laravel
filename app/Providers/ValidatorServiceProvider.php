<?php namespace tcCore\Providers;

use Illuminate\Support\ServiceProvider;

class ValidatorServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->app['validator']->extend('dummy', function ($attribute, $value, $parameters)
        {
            return true;
        });
    }

    public function register()
    {
        //
    }
}