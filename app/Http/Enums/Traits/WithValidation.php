<?php

namespace tcCore\Http\Enums\Traits;

use Illuminate\Support\Str;

trait WithValidation
{

    public function validateValue($value)
    {
        $validationMethod = sprintf('validate%s', Str::pascal($this->value));
        if (
            ($value !== false && $value !== null)
            && method_exists(self::class, $validationMethod)
        ) {
            return self::$validationMethod($value);
        }
        return $value;
    }
}