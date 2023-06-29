<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\Icon;
use tcCore\Http\Enums\Traits\WithAttributes;
use tcCore\Http\Enums\Traits\WithIconAttribute;

enum CommentEmoji: string
{
    use WithAttributes, WithIconAttribute;

    #[Icon('icon.checkmark-emoji')]
    case CHECK_MARK = 'check mark';
    #[Icon('icon.crossmark-emoji')]
    case CROSS_MARK = 'cross mark';
    #[Icon('icon.questionmark-emoji')]
    case QUESTION_MARK = 'question mark';
    #[Icon('icon.congratulations')]
    case CONGRATULATIONS = 'congratulations';
    #[Icon('icon.thumbs-up')]
    case THUMBS_UP = 'thumbs up';
    #[Icon('icon.thumbs-down')]
    case THUMBS_DOWN = 'thumbs down';
    #[Icon('icon.smiley-happy-trafficlight')]
    case SMILEY_HAPPY = 'smiley happy';
    #[Icon('icon.smiley-neutral-trafficlight')]
    case SMILEY_NEUTRAL = 'smiley neutral';
    #[Icon('icon.smiley-sad-trafficlight')]
    case SMILEY_SAD = 'smiley sad';
}