<?php

namespace tcCore\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MacrosServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        QueryBuilder::macro('havingCount', function ($column, $operator, $amount) {
            return $this->havingRaw("COUNT($column) $operator $amount");
        });

        EloquentBuilder::macro('optionList', function () {
            return $this->get(['id', 'name'])->map(function ($value) {
                return (object) ['id' => $value->id, 'value' => $value->name];
            });
        });

        Str::macro('dotToPascal', function ($string) {
            return Str::of($string)->replace('.','_')->camel()->ucfirst();
        });
    }
}