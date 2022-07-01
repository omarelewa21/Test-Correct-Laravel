<?php

namespace tcCore\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

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
    }
}