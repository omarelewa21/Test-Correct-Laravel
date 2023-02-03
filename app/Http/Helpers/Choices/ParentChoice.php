<?php

namespace tcCore\Http\Helpers\Choices;

class ParentChoice extends Choice
{
    public function __construct(string|int $value, string|int $label, array $customProperties)
    {
        $customProperties = $customProperties + ['parent' => true];
        parent::__construct($value, $label, $customProperties);
    }
}