<?php

namespace tcCore\Http\Enums;

use Illuminate\Support\Str;

enum UserFeatureSetting: string
{
    case HAS_PUBLISHED_TEST = 'has_published_test';

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

    public function castValue($callback)
    {
        $castMethod = sprintf('cast%s', Str::pascal($this->value));
        if (method_exists(self::class, $castMethod)) {
            return self::$castMethod(
                $callback()
            );
        }
        return false;
    }
}
