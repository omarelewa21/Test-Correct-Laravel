<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\Description;
use tcCore\Http\Enums\Attributes\Order;
use tcCore\Http\Enums\Traits\WithAttributes;

enum WordType: string
{
    use WithAttributes;

    #[Order(1)]
    #[Description('vak')]
    case SUBJECT = 'subject';
    #[Order(2)]
    #[Description('vertaling')]
    case TRANSLATION = 'translation';
    #[Order(3)]
    #[Description('definitie')]
    case DEFINITION = 'definition';
    #[Order(4)]
    #[Description('synoniem')]
    case SYNONYM = 'synonym';

}
