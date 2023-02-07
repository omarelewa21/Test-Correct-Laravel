<?php

namespace tcCore\Http\Helpers\Choices;

class Choice
{
    public function __construct(
        public string|int $value,
        public string|int $label,
        public array|null $customProperties = []
    ) {}

    public static function build(
        string|int $value,
        string|int $label,
        array|null $customProperties = []
    ): array
    {
        return (array)new static($value, $label, $customProperties);
    }
}