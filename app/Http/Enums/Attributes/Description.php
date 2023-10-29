<?php

namespace tcCore\Http\Enums\Attributes;

use Attribute;

#[Attribute]
class Description
{
    public string $description;

    /* Translation key should be passed, so it can be translated directly */
    public function __construct(
        string $translationKey,
    ) {
        $this->description = __($translationKey);
    }
}