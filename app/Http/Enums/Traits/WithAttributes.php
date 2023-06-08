<?php

namespace tcCore\Http\Enums\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClassConstant;
use tcCore\Http\Enums\Attributes\Description;
use tcCore\Http\Enums\Attributes\Color;
use tcCore\Http\Enums\Attributes\Initial;
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
        return __($instance->translationKey);
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

    public function getHexColorCode($opacity = 1)
    {
        $this->validateOpacityValue($opacity);

        $instance = self::getAttributeInstance($this, Color::class);
        if (!$instance) {
            return null;
        }

        $red = Str::padLeft(dechex($instance->red), 2, '0');
        $green = Str::padLeft(dechex($instance->green), 2, '0');
        $blue = Str::padLeft(dechex($instance->blue), 2, '0');

        if($opacity >= 1) {
            return sprintf('#%s%s%s', $red, $green, $blue);
        }

        $opacity = Str::padLeft(dechex(round($opacity*255)), 2, '0');

        return sprintf('#%s%s%s%s', $red, $green, $blue, $opacity);
    }

    public function getRgbColorCode($opacity = 1)
    {
        $this->validateOpacityValue($opacity);

        $instance = self::getAttributeInstance($this, Color::class);
        if (!$instance) {
            return null;
        }
        return sprintf('rgba(%s,%s,%s,%s)', $instance->red, $instance->green, $instance->blue, $opacity);
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

    private function validateOpacityValue($opacity)
    {
        if(!((is_float($opacity)) || is_int($opacity)) || $opacity > 1 || $opacity < 0) {
            throw new \Exception('Invalid opacity value. Please use a value between 0 and 1');
        }
    }
}