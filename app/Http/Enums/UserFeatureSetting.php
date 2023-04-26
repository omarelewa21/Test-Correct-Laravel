<?php

namespace tcCore\Http\Enums;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use tcCore\Exceptions\AccountSettingException;
use tcCore\Http\Enums\Attributes\Initial;
use tcCore\Http\Enums\Traits\WithAttributes;

enum UserFeatureSetting: string
{
    use WithAttributes;

    #[Initial(false)]
    case HAS_PUBLISHED_TEST = 'has_published_test';
    #[Initial(true)]
    case ENABLE_AUTO_LOGOUT = 'enable_auto_logout';
    #[Initial(15)]
    case AUTO_LOGOUT_MINUTES = 'auto_logout_minutes';

    case SYSTEM_LANGUAGE = 'system_language';

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
            return self::$castMethod($callback());
        }
        return false;
    }

    public static function initialValues(): Collection
    {
        return collect(self::cases())
            ->mapWithKeys(function ($enum) {
                return [$enum->value => self::getInitialValue($enum)];
            });
    }

    private function validateAutoLogoutMinutes($value): bool
    {
        if ($value >= 15 && $value <= 120)  {
            return true;
        }
        throw new AccountSettingException(sprintf('%s heeft geen correcte waarde', $this->value));
    }
}
