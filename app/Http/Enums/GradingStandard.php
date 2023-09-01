<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\Description;
use tcCore\Http\Enums\Attributes\Initial;
use tcCore\Http\Enums\Traits\WithAttributes;
use tcCore\Http\Enums\Traits\WithCasting;
use tcCore\Http\Enums\Traits\WithValidation;
use tcCore\TestTake;

enum GradingStandard: string implements FeatureSettingKey
{
    use WithAttributes;
    use WithValidation;
    use WithCasting;

    #[Initial(1)]
    #[Description('grading.good_per_point')]
    case GOOD_PER_POINT = 'good_per_point';
    #[Initial(1)]
    #[Description('grading.errors_per_point')]
    case ERRORS_PER_POINT = 'errors_per_point';
    #[Initial(7.5)]
    #[Description('grading.average')]
    case AVERAGE = 'average';
    #[Initial(1)]
    #[Description('grading.n_term')]
    case N_TERM = 'n_term';
    #[Initial(50)]
    #[Description('grading.cesuur')]
    case CESUUR = 'cesuur';

    public const conversionArray = [
        'ppp'            => self::GOOD_PER_POINT,
        'epp'            => self::ERRORS_PER_POINT,
        'wanted_average' => self::AVERAGE,
        'n_term'         => self::N_TERM,
        'pass_mark'      => self::CESUUR,
    ];

    public static function getEnumFromTestTake(TestTake $testTake): array
    {
        $standardizationValues = collect($testTake->toArray())
            ->only(array_keys(self::conversionArray))
            ->filter();

        $value = $standardizationValues->first();

        if ($standardizationValues->keys()->first() === 'n_term' && $standardizationValues->has('pass_mark')) {
            $passMark = $standardizationValues->get('pass_mark');
            return [$value, self::CESUUR, $passMark];
        }

        $method = $standardizationValues->keys()->first();
        return [$value, self::fromDatabaseToEnum($method), null];
    }

    public static function fromDatabaseToEnum($value): GradingStandard
    {
        return self::conversionArray[$value];
    }

    public function toDatabaseAttribute(): string
    {
        return collect(self::conversionArray)
            ->filter(fn($value) => $value === $this)
            ->keys()
            ->first();
    }
}
