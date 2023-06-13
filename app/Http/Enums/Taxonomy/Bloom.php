<?php

namespace tcCore\Http\Enums\Taxonomy;

use tcCore\Http\Enums\Traits\WithTaxonomyMethods;

enum Bloom: string implements Taxonomy
{
    use WithTaxonomyMethods;

    case Onthouden = 'Onthouden';
    case Begrijpen = 'Begrijpen';
    case Toepassen = 'Toepassen';
    case Analyseren = 'Analyseren';
    case Evalueren = 'Evalueren';
    case Creeren = 'Creëren';

    public static function columnName(): string
    {
        return 'bloom';
    }

    public static function displayName(): string
    {
        return 'Bloom';
    }
}
