<?php

namespace tcCore\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\Normalize;

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
        EloquentBuilder::macro('whereUuidIn', function (array|Collection $uuids) {
            $uuidSearchString = collect($uuids)
                ->map(function ($uuid) {
                    if (!Uuid::isValid($uuid)) {
                        throw new \Exception('Trying to search with a non-uuid.');
                    }
                    return sprintf("unhex('%s')", str($uuid)->replace('-', ''));
                })
                ->join(', ');
            $whereClause = sprintf("%s.uuid in (%s)", $this->getModel()->getTable(), $uuidSearchString);
            return $this->whereRaw($whereClause);
        });

        Str::macro('dotToPascal', function ($string) {
            return Str::of($string)->replace('.', '_')->camel()->ucfirst();
        });
        Str::macro('pascal', function ($string) {
            return Str::of($string)->studly();
        });

        Str::macro('plainTextWordCount', function ($string) {
            $operations = collect([
                // Insert a space after each closing HTML tag
                fn($text) => preg_replace('/<\/[^>]+>/', '$0 ', $text),
                fn($text) => html_entity_decode(strip_tags($text)),
                // Replace accent characters with their normal equivalent eg: é => e
                fn($text) => preg_replace('/\pM/u', '', \Normalizer::normalize($text, \Normalizer::FORM_D)),
                fn($text) => str_replace("ß", "ss", $text),
                // Replace apostrophe (for words like: L'Éclat or dell'italia)
                fn($text) => str_replace("'", "", $text),
                // Replace dots and commas in numbers (for valuta)
                fn($text) => preg_replace('/(?<=\d)[.,-](?=\d|-)/', '', $text),
                // Replace non-word characters
                fn($text) => preg_replace('/[\W_]+/', ' ', $text),
                fn($text) => trim($text),
            ]);

            $transformedText = $operations->reduce(fn($carry, $callback) => $callback($carry), $string);

            if($transformedText === '') return 0;

            return count(explode(' ', $transformedText) ?: []);
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
        Collection::macro('discrepancyFiltered', function (bool $hideNonDiscrepancy) {
            return $this->when($hideNonDiscrepancy, fn($answers) => $answers->whereNot('hasDiscrepancy', false, true));
        });

        //implements Eloquent Builder methods into
        Collection::macro('onlyTrashed', function () {
            return $this->whereNotNull('deleted_at');
        });
        Collection::macro('withoutTrashed', function () {
            return $this->whereNull('deleted_at');
        });
        Collection::macro('replaceWithNewKey', function ($oldKey, $newKey, $newValue) {
            return $this->map(fn($value, $key) => $key === $oldKey ? [$newKey => $newValue] : [$key => $value])
                ->collapse();
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

        DB::macro(
            'unionRaw',
            function (
                QueryBuilder|EloquentBuilder $queryA,
                QueryBuilder|EloquentBuilder $queryB,
                string                       $unionName = 'unioned'
            ): QueryBuilder {
                $queryA = $queryA->toBase();
                $queryB = $queryB->toBase();
                return DB::query()
                    ->from(DB::raw("({$queryA->toSql()} UNION {$queryB->toSql()}) as {$unionName}"))
                    ->mergeBindings($queryA)
                    ->mergeBindings($queryB);
            }
        );
    }
}