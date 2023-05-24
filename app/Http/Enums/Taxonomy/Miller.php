<?php

namespace tcCore\Http\Enums\Taxonomy;

use tcCore\Http\Enums\Traits\WithTaxonomyMethods;

enum Miller: string implements Taxonomy
{
    use WithTaxonomyMethods;

    case Weten = 'Weten';
    case WetenHoe = 'Weten hoe';
    case LatenZien = 'Laten zien';
    case Doen = 'Doen';

    public static function columnName(): string
    {
        return 'miller';
    }

    public static function displayName(): string
    {
        return 'Miller';
    }
}
