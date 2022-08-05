<?php

namespace tcCore\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MacrosServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        Builder::macro('havingCount', function ($column, $operator, $amount) {
            return $this->havingRaw("COUNT($column) $operator $amount");
        });

        Str::macro('dotToPascal', function ($string) {
            return Str::of($string)->replace('.','_')->camel()->ucfirst();
        });
    }
}