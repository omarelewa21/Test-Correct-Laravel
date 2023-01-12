<?php

namespace tcCore\Http\Enums\Taxonomy;

enum Rtti: string implements Taxonomy
{
    use WithTaxonomyMethods;

    case R = 'R';
    case T1 = 'T1';
    case T2 = 'T2';
    case I = 'I';

    public static function columnName(): string
    {
        return 'rtti';
    }

    public static function displayName(): string
    {
        return 'RTTI';
    }
}
