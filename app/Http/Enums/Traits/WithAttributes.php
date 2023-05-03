<?php

namespace tcCore\Http\Enums\Traits;

use Illuminate\Support\Collection;
use ReflectionClassConstant;
use tcCore\Http\Enums\Attributes\Description;
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

    public static function getDescription(self $enum): ?string
    {
        $ref = new ReflectionClassConstant(self::class, $enum->name);
        $classAttributes = $ref->getAttributes(Description::class);

        if (count($classAttributes) === 0) {
            return null;
        }
        return __($classAttributes[0]->newInstance()->translationKey);
    }

    public static function casesWithDescription(): Collection
    {
        return collect(self::cases())
            ->mapWithKeys(function ($enum) {
                return [$enum->value => self::getDescription($enum)];
            });
    }
}