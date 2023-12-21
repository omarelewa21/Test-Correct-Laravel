<?php

namespace tcCore\Http\Enums\Traits;

use Illuminate\Support\Collection;
use ReflectionClassConstant;
use tcCore\Http\Enums\Attributes\Description;
use tcCore\Http\Enums\Attributes\Initial;
use tcCore\Http\Enums\Attributes\Order;
use tcCore\Http\Enums\Attributes\Type;

trait WithAttributes
{
    public function initialValue(): mixed
    {
        return $this->castValue(self::getInitialValue($this));
    }

    public static function getInitialValue(self $enum): mixed
    {
        $instance = self::getAttributeInstance($enum, Initial::class);
        if (!$instance) {
            return null;
        }
        return $instance->initial;
    }

    public static function getDescription(self $enum): ?string
    {
        $instance = self::getAttributeInstance($enum, Description::class);
        if (!$instance) {
            return null;
        }
        return $instance->description;
    }

    public static function casesWithDescription(): Collection
    {
        return collect(self::cases())
            ->mapWithKeys(function ($enum) {
                return [$enum->value => self::getDescription($enum)];
            });
    }

    public function getType()
    {
        $instance = self::getAttributeInstance($this, Type::class);
        if (!$instance) {
            return null;
        }
        return $instance->type;
    }
    private static function getAttributeInstance(self $enum, $attributeClass)
    {
        $ref = new ReflectionClassConstant(self::class, $enum->name);
        $classAttributes = $ref->getAttributes($attributeClass);

        if (count($classAttributes) === 0) {
            return null;
        }
        return $classAttributes[0]->newInstance();
    }

    public function getOrder()
    {
        $instance = self::getAttributeInstance($this, Order::class);
        if (!$instance) {
            return null;
        }
        return $instance->order;
    }
}