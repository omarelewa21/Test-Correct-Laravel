<?php

namespace tcCore\Http\Enums;

use Illuminate\Support\Collection;
use tcCore\Exceptions\AccountSettingException;
use tcCore\Http\Enums\Attributes\Initial;
use tcCore\Http\Enums\Attributes\Type;
use tcCore\Http\Enums\Traits\WithAttributes;
use tcCore\Http\Enums\Traits\WithCasting;
use tcCore\Http\Enums\Traits\WithValidation;

/* Implement the correct interface so they enum is a valid title key */
enum UserSystemSetting: string implements FeatureSettingKey
{
    use WithAttributes;
    use WithValidation;
    use WithCasting;

    /* Why did you change this to 'int' values? */
    #[Initial(false)]
    #[Type('bool')]
    case ALLOW_NEW_CO_LEARNING_TEACHER = 'allow_new_co_learning_teacher';
    #[Initial(false)]
    #[Type('bool')]
    case ALLOW_NEW_ASSESSMENT = 'allow_new_assessment';

    /* This can be abstracted because it is duplicate code */
    public static function initialValues(): Collection
    {
        return collect(self::cases())->mapWithKeys(fn($enum) => [$enum->value => self::getInitialValue($enum)]);
    }

}
