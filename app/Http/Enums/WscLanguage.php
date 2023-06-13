<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\Description;
use tcCore\Http\Enums\Traits\WithAttributes;

enum WscLanguage: string
{
    use WithAttributes;

    #[Description('lang.en_GB')]
    case ENGLISH = 'en_GB';
    #[Description('lang.nl_NL')]
    case DUTCH = 'nl_NL';
    #[Description('lang.fr_FR')]
    case FRENCH = 'fr_FR';
    #[Description('lang.de_DE')]
    case GERMAN = 'de_DE';
    #[Description('lang.es_ES')]
    case SPANISH = 'es_ES';
    #[Description('lang.it_IT')]
    case ITALIAN = 'it_IT';
}
