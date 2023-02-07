<?php

namespace tcCore\Http\Helpers\Choices;

class ChildChoice extends Choice
{
    public function __construct(string|int $value, string|int $label, array $customProperties)
    {
        $customProperties = $customProperties + ['parent' => false];
        parent::__construct($value, $label, $customProperties);
    }
}