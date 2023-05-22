<?php

namespace tcCore\Http\Enums\Traits;

use Illuminate\Support\Str;

trait WithCasting
{
    public function castValue($value)
    {
        $castMethod = sprintf('cast%s', Str::pascal($this->value));
        if (method_exists(self::class, $castMethod)) {
            return self::$castMethod($value);
        }
        if (!is_null($value) && $this->needsTypeCasting()) {
            return $this->castToType($value);
        }
        return $value;
    }

    private function needsTypeCasting(): bool
    {
        return !is_null($this->getType());
    }

    private function castToType($value)
    {
        if ($this->getType() === 'bool') {
            return (bool)$value;
        }
        if ($this->getType() === 'int') {
            return (int)$value;
        }
        return $value;
    }
}