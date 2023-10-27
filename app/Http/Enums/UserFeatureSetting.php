<?php

namespace tcCore\Http\Enums;

use Illuminate\Support\Collection;
use tcCore\Exceptions\AccountSettingException;
use tcCore\Http\Enums\Attributes\Initial;
use tcCore\Http\Enums\Attributes\Type;
use tcCore\Http\Enums\Traits\WithAttributes;
use tcCore\Http\Enums\Traits\WithCasting;
use tcCore\Http\Enums\Traits\WithValidation;

enum UserFeatureSetting: string implements FeatureSettingKey
{
    use WithAttributes;
    use WithValidation;
    use WithCasting;

    #[Initial(false)]
    #[Type('bool')]
    case HAS_PUBLISHED_TEST = 'has_published_test';
    #[Initial(true)]
    #[Type('bool')]
    case ENABLE_AUTO_LOGOUT = 'enable_auto_logout';
    #[Initial(15)]
    #[Type('int')]
    case AUTO_LOGOUT_MINUTES = 'auto_logout_minutes';
    #[Initial('nl')]
    case SYSTEM_LANGUAGE = 'system_language';
    #[Initial('nl_NL')]
    case WSC_DEFAULT_LANGUAGE = 'wsc_default_language';
    #[Initial(true)]
    #[Type('bool')]
    case WSC_COPY_SUBJECT_LANGUAGE = 'wsc_copy_subject_language';
    #[Initial(true)]
    #[Type('bool')]
    case QUESTION_PUBLICLY_AVAILABLE = 'question_publicly_available';
    #[Initial(1)]
    #[Type('int')]
    case QUESTION_DEFAULT_POINTS = 'question_default_points';
    #[Initial(true)]
    #[Type('bool')]
    case QUESTION_HALF_POINTS_POSSIBLE = 'question_half_points_possible';
    #[Initial(true)]
    #[Type('bool')]
    case QUESTION_AUTO_SCORE_COMPLETION = 'question_auto_score_completion';
    #[Initial(1)]
    #[Type('int')]
    case TEST_TAKE_DEFAULT_WEIGHT = 'test_take_default_weight';
    #[Initial(true)]
    #[Type('bool')]
    case TEST_TAKE_BROWSER_TESTING = 'test_take_browser_testing';
    #[Initial(false)]
    #[Type('bool')]
    case TEST_TAKE_TEST_DIRECT = 'test_take_test_direct';
    #[Initial(false)]
    #[Type('bool')]
    case TEST_TAKE_NOTIFY_STUDENTS = 'test_take_notify_students';
    #[Initial(false)]
    #[Type('bool')]
    case ASSESSMENT_SKIP_NO_DISCREPANCY_ANSWER = 'assessment_skip_no_discrepancy_answer';
    #[Initial(false)]
    #[Type('bool')]
    case ASSESSMENT_SHOW_STUDENT_NAMES = 'assessment_show_student_names';
    #[Initial(true)]
    #[Type('bool')]
    case REVIEW_SHOW_GRADES = 'review_show_grades';
    #[Initial(true)]
    #[Type('bool')]
    case REVIEW_SHOW_CORRECTION_MODEL = 'review_show_correction_model';
    #[Initial('n_term')]
    case GRADE_DEFAULT_STANDARD = 'grade_default_standard';
    #[Initial(1)]
    case GRADE_STANDARD_VALUE = 'grade_standard_value';
    #[Initial(50)]
    #[Type('int')]
    case GRADE_CESUUR_PERCENTAGE = 'grade_cesuur_percentage';
    #[Initial(false)]
    #[Type('bool')]
    case SEEN_ASSESSMENT_NOTIFICATION = 'seen_assessment_notification';
    #[Initial(false)]
    #[Type('bool')]
    case SPELL_CHECK_AVAILABLE_DEFAULT = 'spell_check_available_default';
    #[Initial(false)]
    #[Type('bool')]
    case MATHML_FUNCTIONS_DEFAULT = 'mathml_functions_default';
    #[Initial(false)]
    #[Type('bool')]
    case RESTRICT_WORD_AMOUNT_DEFAULT = 'restrict_word_amount_default';
    #[Initial(0)]
    #[Type('int')]
    case MAX_WORDS_DEFAULT = 'max_words_default';
    #[Initial(false)]
    #[Type('bool')]
    case TEXT_FORMATTING_DEFAULT = 'text_formatting_default';

    public static function initialValues(): Collection
    {
        return collect(self::cases())->mapWithKeys(fn($enum) => [$enum->value => self::getInitialValue($enum)]);
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

    private function castSystemLanguage($value): SystemLanguage
    {
        return SystemLanguage::tryFrom($value) ?? SystemLanguage::DUTCH;
    }

    private function castWscLanguage($value): WscLanguage
    {
        return WscLanguage::tryFrom($value) ?? WscLanguage::DUTCH;
    }

    private function castGradeDefaultStandard($value): GradingStandard
    {
        return GradingStandard::tryFrom($value) ?? GradingStandard::N_TERM;
    }
}
