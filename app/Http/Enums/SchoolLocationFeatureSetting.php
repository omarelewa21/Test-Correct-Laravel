<?php

namespace tcCore\Http\Enums;

use Illuminate\Support\Str;

enum SchoolLocationFeatureSetting: string
{
    case TEST_PACKAGE = 'test_package';
    case ALLOW_ANALYSES = 'allow_analyses';
    case ALLOW_NEW_TAKEN_TESTS_PAGE = 'allow_new_taken_tests_page';
    case ALLOW_NEW_CO_LEARNING = 'allow_new_co_learning';
    case ALLOW_NEW_CO_LEARNING_TEACHER = 'allow_new_co_learning_teacher';
    case ALLOW_CREATHLON = 'allow_creathlon';
    case ALLOW_OLYMPIADE = 'allow_olympiade';
    case ALLOW_NEW_ASSESSMENT = 'allow_new_assessment';

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

    public static function settingToDefaultSchool()
    {
        return collect([
//            self::TEST_PACKAGE => TestPackages::None,
            self::ALLOW_ANALYSES,
            self::ALLOW_NEW_TAKEN_TESTS_PAGE,
            self::ALLOW_NEW_CO_LEARNING,
            self::ALLOW_NEW_CO_LEARNING_TEACHER,
//            self::ALLOW_CREATHLON => false,
//            self::ALLOW_OLYMPIADE => false,
            self::ALLOW_NEW_ASSESSMENT,
        ]);
    }
}
