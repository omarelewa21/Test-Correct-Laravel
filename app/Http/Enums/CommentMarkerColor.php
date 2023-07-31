<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\Color;
use tcCore\Http\Enums\Traits\WithAttributes;
use tcCore\Http\Enums\Traits\WithColorAttribute;

enum CommentMarkerColor: string
{
    use WithAttributes, WithColorAttribute;

    #[Color(71, 129, 255)]
    case BLUE = 'blue';
    #[Color(117, 222, 138)]
    case GREEN = 'green';
    #[Color(221, 132, 255)]
    case PURPLE = 'purple';
    #[Color(255, 208, 132 )]
    case ORANGE = 'orange';
    #[Color(255, 132, 132)]
    case RED = 'red';
    #[Color(132, 255, 224)]
    case MINT = 'mint';
}
