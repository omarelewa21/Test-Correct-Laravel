<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\Description;
use tcCore\Http\Enums\Attributes\Initial;
use tcCore\Http\Enums\Traits\WithAttributes;

enum GradingStandard: string implements FeatureSettingKey
{
    use WithAttributes;

    #[Initial(1)]
    #[Description('grading.good_per_point')]
    case GOOD_PER_POINT = 'good_per_point';
    #[Initial(1)]
    #[Description('grading.mistakes_per_point')]
    case MISTAKES_PER_POINT = 'mistakes_per_point';
    #[Initial(7.5)]
    #[Description('grading.mean')]
    case MEAN = 'mean';
    #[Initial(1)]
    #[Description('grading.n_term')]
    case N_TERM = 'n_term';
    #[Initial(50)]
    #[Description('grading.cesuur')]
    case CESUUR = 'cesuur';
}
