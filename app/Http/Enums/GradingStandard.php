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

    public static function methodConverter(TestTake $testTake): ?GradingStandard
    {
        $method = collect(
            TestTake::whereId($testTake->getKey())
                ->get(['ppp', 'epp', 'wanted_average', 'n_term', 'pass_mark'])
                ->first()
                ->toArray()
        )
            ->filter()
            ->keys()
            ->first();
        return match ($method) {
            'ppp'            => self::GOOD_PER_POINT,
            'epp'            => self::ERRORS_PER_POINT,
            'wanted_average' => self::AVERAGE,
            'n_term'         => self::N_TERM,
            'pass_mark'      => self::CESUUR,
            default          => null,
        };
    }
}
