<?php

namespace tcCore\Http\Enums;

use Illuminate\Support\Str;
use tcCore\Http\Enums\Attributes\Type;
use tcCore\Http\Enums\Traits\WithAttributes;
use tcCore\Http\Enums\Traits\WithCasting;
use tcCore\Http\Enums\Traits\WithValidation;

enum SchoolLocationFeatureSetting: string
{
    use WithAttributes;
    use WithValidation;
    use WithCasting;

    case TEST_PACKAGE = 'test_package';
    #[Type('bool')]
    case ALLOW_ANALYSES = 'allow_analyses';
    #[Type('bool')]
    case ALLOW_NEW_TAKEN_TESTS_PAGE = 'allow_new_taken_tests_page';
    #[Type('bool')]
    case ALLOW_NEW_CO_LEARNING = 'allow_new_co_learning';
    #[Type('bool')]
    case ALLOW_NEW_CO_LEARNING_TEACHER = 'allow_new_co_learning_teacher';
    #[Type('bool')]
    case ALLOW_CREATHLON = 'allow_creathlon';
    #[Type('bool')]
    case ALLOW_OLYMPIADE = 'allow_olympiade';
    #[Type('bool')]
    case ALLOW_NEW_ASSESSMENT = 'allow_new_assessment';
    #[Type('bool')]
    case ALLOW_NEW_REVIEWING = 'allow_new_reviewing';
    #[Type('bool')]
    case ALLOW_CMS_WRITE_DOWN_WSC_TOGGLE = 'allow_cms_write_down_wsc_toggle';


    public static function validateTestPackage(TestPackages|string $testPackage): string|false
    {
        if (is_string($testPackage)) {
            $name = $testPackage;
            $testPackage = TestPackages::from(Str::lower($testPackage));

            if (is_null($testPackage)) {
                throw new \Exception(sprintf('Invalid TestPackage: "%s".', $name));
            }
        }
        if ($testPackage === TestPackages::None) {
            return false;
        }

        return $testPackage->value;
    }

    public static function castTestPackage($testPackage)
    {
        return TestPackages::tryFrom($testPackage) ?? TestPackages::None;
    }
}
