<?php

namespace tcCore\Http\Enums\Attributes;

use Attribute;

#[Attribute]
class Color
{
    public function __construct(
        public int $red,
        public int $green,
        public int $blue,
    ) {}
}