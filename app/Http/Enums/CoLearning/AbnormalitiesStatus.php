<?php

namespace tcCore\Http\Enums\CoLearning;

enum AbnormalitiesStatus: string
{
    case Happy   = 'happy';
    case Neutral = 'neutral';
    case Sad     = 'sad';
    case Default = 'default';
}
