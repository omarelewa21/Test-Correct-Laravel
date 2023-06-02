<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\Description;
use tcCore\Http\Enums\Traits\WithAttributes;

enum SystemLanguage: string
{
    use WithAttributes;
    #[Description('lang.en_GB')]
    case ENGLISH = 'en';

    #[Description('lang.nl_NL')]
    case DUTCH = 'nl';
}
