<?php

namespace tcCore\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

        EloquentBuilder::macro('optionList', function ($cols = ['id','name'], $labelCallback = null) {
            return $this->get($cols)->map(function ($value) use ($labelCallback){
                return (object) ['value' => $value->id, 'label' => ($labelCallback) ? $labelCallback($value) : $value->name];
            });
        });

        Str::macro('dotToPascal', function ($string) {
            return Str::of($string)->replace('.','_')->camel()->ucfirst();
        });
        Str::macro('pascal', function ($string) {
            return Str::of($string)->studly();
        });

        Collection::macro('append', function (...$values) {
            return $this->push(...$values);
        });
    }
}