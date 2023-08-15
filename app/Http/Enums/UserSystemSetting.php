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

    public static function getInitialValues(): Collection
    {
        return collect([
            self::ALLOW_NEW_CO_LEARNING_TEACHER => true,
            self::ALLOW_NEW_ASSESSMENT => true,
        ]);
    }

    public static function getValidationRules(): Collection
    {
        return collect([
            self::ALLOW_NEW_CO_LEARNING_TEACHER => 'required|boolean',
            self::ALLOW_NEW_ASSESSMENT => 'required|boolean',
        ]);
    }

    public static function getValidationMessages(): Collection
    {
        return collect([
            self::ALLOW_NEW_CO_LEARNING_TEACHER => [
                'required' => 'The allow new co-learning teacher field is required.',
                'boolean' => 'The allow new co-learning teacher field must be true or false.',
            ],
            self::ALLOW_NEW_ASSESSMENT => [
                'required' => 'The allow new assessment field is required.',
                'boolean' => 'The allow new assessment field must be true or false.',
            ],
        ]);
    }
}
