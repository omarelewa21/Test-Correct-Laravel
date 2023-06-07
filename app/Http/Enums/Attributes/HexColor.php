<?php

namespace tcCore\Http\Enums\Attributes;

use Attribute;

#[Attribute]
class HexColor
{
    public function __construct(
        public string $hexValue,
    ) {}
}