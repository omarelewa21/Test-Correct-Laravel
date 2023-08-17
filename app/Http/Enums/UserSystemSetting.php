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

    #[Initial(1)]
    #[Type('int')]
    case ALLOW_NEW_CO_LEARNING_TEACHER = 'allow_new_co_learning_teacher';
    #[Initial(1)]
    #[Type('int')]
    case ALLOW_NEW_ASSESSMENT = 'allow_new_assessment';

    public static function initialValues(): Collection
    {
        return collect(self::cases())->mapWithKeys(fn($enum) => [$enum->value => self::getInitialValue($enum)]);
    }
    
    public static function getValidationRules(): Collection
    {
        return collect([
            self::ALLOW_NEW_CO_LEARNING_TEACHER => 'required|in:0,1',
            self::ALLOW_NEW_ASSESSMENT => 'required|in:0,1',
        ]);
    }

    public static function getValidationMessages(): Collection
    {
        return collect([
            self::ALLOW_NEW_CO_LEARNING_TEACHER => [
                'required' => 'The allow new co-learning teacher field is required.',
                'in' => 'The allow new co-learning teacher field must be 0 or 1.',
            ],
            self::ALLOW_NEW_ASSESSMENT => [
                'required' => 'The allow new assessment field is required.',
                'in' => 'The allow new assessment field must be 0 or 1.',
            ],
        ]);
    }
}
