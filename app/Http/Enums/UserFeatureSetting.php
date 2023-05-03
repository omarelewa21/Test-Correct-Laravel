<?php

namespace tcCore\Http\Enums;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use tcCore\Exceptions\AccountSettingException;
use tcCore\Http\Enums\Attributes\Initial;
use tcCore\Http\Enums\Traits\WithAttributes;

enum UserFeatureSetting: string implements FeatureSettingKey
{
    use WithAttributes;

    #[Initial(false)]
    case HAS_PUBLISHED_TEST = 'has_published_test';
    #[Initial(true)]
    case ENABLE_AUTO_LOGOUT = 'enable_auto_logout';
    #[Initial(15)]
    case AUTO_LOGOUT_MINUTES = 'auto_logout_minutes';
    #[Initial('nl')]
    case SYSTEM_LANGUAGE = 'system_language';
    #[Initial('nl_NL')]
    case WSC_DEFAULT_LANGUAGE = 'wsc_default_language';
    #[Initial(true)]
    case WSC_COPY_SUBJECT_LANGUAGE = 'wsc_copy_subject_language';
    #[Initial(true)]
    case QUESTION_PUBLICLY_AVAILABLE = 'question_publicly_available';
    #[Initial(1)]
    case QUESTION_DEFAULT_POINTS = 'question_default_points';
    #[Initial(true)]
    case QUESTION_HALF_POINTS_POSSIBLE = 'question_half_points_possible';
    #[Initial(true)]
    case QUESTION_AUTO_SCORE_COMPLETION = 'question_auto_score_completion';
    #[Initial(1)]
    case TEST_TAKE_DEFAULT_WEIGHT = 'test_take_default_weight';
    #[Initial(true)]
    case TEST_TAKE_BROWSER_TESTING = 'test_take_browser_testing';
    #[Initial(false)]
    case TEST_TAKE_TEST_DIRECT = 'test_take_test_direct';
    #[Initial(false)]
    case TEST_TAKE_NOTIFY_STUDENTS = 'test_take_notify_students';
    #[Initial(false)]
    case ASSESSMENT_SKIP_NO_DISCREPANCY_ANSWER = 'assessment_skip_no_discrepancy_answer';
    #[Initial(false)]
    case ASSESSMENT_SHOW_STUDENT_NAMES = 'assessment_show_student_names';
    #[Initial(true)]
    case REVIEW_SHOW_GRADES = 'review_show_grades';
    #[Initial(true)]
    case REVIEW_SHOW_CORRECTION_MODEL = 'review_show_correction_model';
    #[Initial('n_term')]
    case GRADE_DEFAULT_STANDARD = 'grade_default_standard';
    #[Initial(1)]
    case GRADE_STANDARD_VALUE = 'grade_standard_value';
    #[Initial(50)]
    case GRADE_CESUUR_PERCENTAGE = 'grade_cesuur_percentage';


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
        if ($value >= 15 && $value <= 120) {
            return true;
        }
        throw new AccountSettingException(sprintf('%s heeft geen correcte waarde', $this->value));
    }

    private function validateSystemLanguage($value): bool
    {
        if (!SystemLanguage::tryFrom($value)) {
            throw new AccountSettingException(sprintf('%s heeft geen correcte waarde', $this->value));
        }

        return true;
    }

    private function validateWscLanguage($value): bool
    {
        if (!WscLanguage::tryFrom($value)) {
            throw new AccountSettingException(sprintf('%s heeft geen correcte waarde', $this->value));
        }

        return true;
    }

    private function validateGradeDefaultStandard($value): bool
    {
        if (!GradingStandard::tryFrom($value)) {
            throw new AccountSettingException(sprintf('%s heeft geen correcte waarde', $this->value));
        }

        return true;
    }
}
