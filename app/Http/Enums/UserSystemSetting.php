<?php

namespace tcCore\Http\Enums;

use Illuminate\Support\Collection;
use tcCore\Exceptions\AccountSettingException;
use tcCore\Http\Enums\Attributes\Initial;
use tcCore\Http\Enums\Attributes\Type;
use tcCore\Http\Enums\Traits\WithAttributes;
use tcCore\Http\Enums\Traits\WithCasting;
use tcCore\Http\Enums\Traits\WithValidation;

enum UserSystemSetting: string
{
    use WithAttributes;
    use WithValidation;
    use WithCasting;

    #[Initial(true)]
    #[Type('bool')]
    case ALLOW_NEW_CO_LEARNING_TEACHER = 'allow_new_co_learning_teacher';
    #[Initial(true)]
    #[Type('bool')]
    case ALLOW_NEW_ASSESSMENT = 'allow_new_assessment';

    public static function initialValues(): Collection
    {
        return collect(self::cases())->mapWithKeys(fn ($enum) => [$enum->value => self::getInitialValue($enum)]);
    }

    private function validateAllowNewCoLearningTeacher($value): bool
    {
        if (!is_bool($value)) {
            throw new AccountSettingException(sprintf('%s heeft geen correcte waarde', $this->value));
        }

        return true;
    }

    private function validateAllowNewAssessment($value): bool
    {
        if (!is_bool($value)) {
            throw new AccountSettingException(sprintf('%s heeft geen correcte waarde', $this->value));
        }

        return true;
    }

}
