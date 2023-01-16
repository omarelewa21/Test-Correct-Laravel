<?php

namespace tcCore\Http\Enums\Taxonomy;

trait WithTaxonomyMethods
{
    public static function values(): array
    {
        return collect(static::cases())->map(fn($case) => $case->value)->toArray();
    }
}