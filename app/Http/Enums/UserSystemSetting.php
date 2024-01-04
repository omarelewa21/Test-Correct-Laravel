<?php

namespace tcCore\Http\Enums;

use Illuminate\Support\Collection;
use tcCore\Exceptions\AccountSettingException;
use tcCore\Http\Enums\Attributes\Initial;
use tcCore\Http\Enums\Attributes\Type;
use tcCore\Http\Enums\Traits\WithAttributes;
use tcCore\Http\Enums\Traits\WithCasting;
use tcCore\Http\Enums\Traits\WithValidation;

enum UserSystemSetting: string implements FeatureSettingKey
{
    use WithAttributes;
    use WithValidation;
    use WithCasting;

    #[Initial(false)]
    #[Type('bool')]
    case ALLOW_NEW_TEST_TAKEN_PAGES = 'allow_new_test_taken_pages';

    #[Initial(false)]
    #[Type('bool')]
    case CMS_ADD_RELATION_QUESTION_CONFIRMATION = 'cms_add_relation_question_confirmation';

    public static function initialValues(): Collection
    {
        return collect(self::cases())->mapWithKeys(fn($enum) => [$enum->value => self::getInitialValue($enum)]);
    }

}
