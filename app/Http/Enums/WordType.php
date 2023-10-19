<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\Order;
use tcCore\Http\Enums\Traits\WithAttributes;

enum WordType: string
{
    use WithAttributes;

    #[Order(1)]
    case SUBJECT = 'subject';
    #[Order(2)]
    case TRANSLATION = 'translation';
    #[Order(3)]
    case DEFINITION = 'definition';
    #[Order(4)]
    case SYNONYM = 'synonym';

}
