<?php

namespace tcCore\Http\Enums\Attributes;

use Attribute;

#[Attribute]
class Icon
{
    public function __construct(
        public string $iconName
    ) {}
}