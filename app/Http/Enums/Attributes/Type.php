<?php

namespace tcCore\Http\Enums\Attributes;

use Attribute;

#[Attribute]
class Type
{
    public function __construct(
        public mixed  $type,
    ) {}
}