<?php

namespace tcCore\Http\Enums\Attributes;

use Attribute;

#[Attribute]
class Description
{
    public function __construct(
        public mixed  $translationKey,
    ) {}
}