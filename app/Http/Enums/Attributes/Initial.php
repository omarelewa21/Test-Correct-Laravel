<?php

namespace tcCore\Http\Enums\Attributes;

use Attribute;

#[Attribute]
class Initial
{
    public function __construct(
        public mixed  $initial,
    ) {}
}