<?php

namespace tcCore\Http\Helpers\Choices;

use Illuminate\Support\Collection;

class ParentChoice extends Choice
{
    public function __construct(string|int            $value,
                                string|int            $label,
                                array                 $customProperties,
    ) {
        $customProperties = $customProperties + ['parent' => true];
        parent::__construct($value, $label, $customProperties);
    }

    public static function build(
        string|int $value,
        string|int $label,
        array|null $customProperties = [],
        Collection|array|null $children = [],
    ): array
    {
        $choice = (array)new static($value, $label, $customProperties);
        $choice['children'] = $children;
        return $choice;
    }
}