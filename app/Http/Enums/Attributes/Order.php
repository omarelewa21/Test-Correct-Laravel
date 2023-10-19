<?php

namespace tcCore\Http\Enums\Attributes;

use Attribute;

#[Attribute]
class Order
{
    public function __construct(
        public mixed  $order,
    ) {}
}