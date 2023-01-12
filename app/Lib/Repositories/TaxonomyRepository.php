<?php

namespace tcCore\Lib\Repositories;

use tcCore\Http\Enums\Taxonomy\Bloom;
use tcCore\Http\Enums\Taxonomy\Miller;
use tcCore\Http\Enums\Taxonomy\Rtti;
use tcCore\Http\Helpers\Choices\ChildChoice;
use tcCore\Http\Helpers\Choices\ParentChoice;

class TaxonomyRepository
{
    private static array $availableTaxonomies = [Rtti::class, Bloom::class, Miller::class];

    public static function taxonomies(): array
    {
        return self::$availableTaxonomies;
    }

    public static function taxonomiesNames(): array
    {
        return collect(self::$availableTaxonomies)->map(fn($taxonomy) => $taxonomy::displayName())->toArray();
    }

    public static function taxonomiesWithValues(): array
    {
        return collect(self::$availableTaxonomies)->mapWithKeys(function ($taxonomy) {
            return [$taxonomy::columnName() => $taxonomy::values()];
        })->toArray();
    }

    public static function filterValuesPerTaxonomyGroup(array $values): array
    {
        $allTaxonomiesWithValues = self::taxonomiesWithValues();

        $taxonomyColumnWithSearchValue = [];
        foreach ($values as $value) {
            foreach ($allTaxonomiesWithValues as $taxName => $taxValues) {
                if (in_array($value, $taxValues)) {
                    $taxonomyColumnWithSearchValue[$taxName][] = $value;
                }
            }
        }
        return $taxonomyColumnWithSearchValue;
    }

    public static function choicesOptions(): array
    {
        return collect(self::taxonomies())->flatMap(function ($taxonomy) {
            return collect($taxonomy::values())
                ->map(function ($option) use ($taxonomy) {
                    return ChildChoice::build(
                        value: $option,
                        label: __('cms.' . $option),
                        customProperties: [
                            'parentId'    => $taxonomy::columnName(),
                            'parentLabel' => $taxonomy::displayName()
                        ]
                    );
                })
                ->prepend(
                    ParentChoice::build(
                        value: $taxonomy::columnName(),
                        label: $taxonomy::displayName(),
                        customProperties: ['parentId' => $taxonomy::columnName()]
                    )
                )
                ->toArray();
        })->toArray();
    }
}
