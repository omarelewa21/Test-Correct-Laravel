<?php

namespace tcCore\Http\Enums\Traits;

use ReflectionClassConstant;
use tcCore\Http\Enums\Attributes\Initial;

trait WithAttributes
{
    public static function getInitialValue(self $enum): mixed
    {
        $ref = new ReflectionClassConstant(self::class, $enum->name);
        $classAttributes = $ref->getAttributes(Initial::class);

        if (count($classAttributes) === 0) {
            return null;
        }
        return $classAttributes[0]->newInstance()->initial;
    }
}