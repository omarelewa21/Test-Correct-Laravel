<?php

namespace tcCore\Http\Enums;

enum TestPackages: string
{
    case None = 'none';
    case Basic = 'basic';
    case Pro = 'pro';

    public static function values()
    {
        return collect(self::cases())->map->value->toArray();
    }
}
