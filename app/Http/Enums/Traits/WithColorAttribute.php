<?php

namespace tcCore\Http\Enums\Traits;

use Illuminate\Support\Str;
use tcCore\Http\Enums\Attributes\Color;

trait WithColorAttribute
{
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

    private function validateOpacityValue($opacity)
    {
        if(!((is_float($opacity)) || is_int($opacity)) || $opacity > 1 || $opacity < 0) {
            throw new \Exception('Invalid opacity value. Please use a value between 0 and 1');
        }
    }
}