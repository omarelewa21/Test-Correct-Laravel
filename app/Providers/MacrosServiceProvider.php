<?php

namespace tcCore\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\Http\Helpers\BaseHelper;

class MacrosServiceProvider extends ServiceProvider
{
    public function register() {}

    public function boot()
    {
        QueryBuilder::macro('havingCount', function ($column, $operator, $amount) {
            return $this->havingRaw("COUNT($column) $operator $amount");
        });

        EloquentBuilder::macro('optionList', function ($cols = ['id', 'name'], $labelCallback = null) {
            return $this->get($cols)->map(function ($value) use ($labelCallback) {
                return (object)[
                    'value' => $value->id,
                    'label' => ($labelCallback) ? $labelCallback($value) : $value->name
                ];
            });
        });

        EloquentBuilder::macro('uuidOptionList', function ($cols = ['uuid', 'name'], $labelCallback = null) {
            return $this->get($cols)->map(function ($value) use ($labelCallback) {
                return (object)[
                    'value' => $value->uuid,
                    'label' => ($labelCallback) ? $labelCallback($value) : $value->name
                ];
            });
        });

        Str::macro('dotToPascal', function ($string) {
            return Str::of($string)->replace('.', '_')->camel()->ucfirst();
        });
        Str::macro('pascal', function ($string) {
            return Str::of($string)->studly();
        });

        Collection::macro('append', function (...$values) {
            return $this->push(...$values);
        });
        Collection::macro('whereNot', function (string $property, mixed $value, bool $strict = false) {
            $operator = $strict ? '!==' : '!=';
            return $this->where($property, $operator, $value);
        });
        Collection::macro('discussionTypeFiltered', function (bool $openOnly) {
            return $this->when($openOnly, fn($questions) => $questions->where('isDiscussionTypeOpen', true));
        });
        Collection::macro('discrepancyFiltered', function (bool $hideNonDescrepancy) {
            return $this->when($hideNonDescrepancy, fn($answers) => $answers->whereNot('hasDiscrepancy', false, true));
        });

        //implements Eloquent Builder methods into
        Collection::macro('onlyTrashed', function () {
            return $this->whereNotNull('deleted_at');
        });
        Collection::macro('withoutTrashed', function () {
            return $this->whereNull('deleted_at');
        });

        URL::macro('referrer', function () {
            $path = Livewire::isLivewireRequest()
                ? BaseHelper::getLivewireOriginalPath(request())
                : request()->getRequestUri();
            return [
                'referrer' => [
                    'type' => 'laravel',
                    'page' => $path
                ]
            ];
        });
    }
}