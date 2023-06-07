<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\HexColor;
use tcCore\Http\Enums\Traits\WithAttributes;

enum CommentMarkerColor: string
{
    use WithAttributes;

    #[HexColor('#004df566')]
    case BLUE = 'blue';
    #[HexColor('#75de8a')]
    case GREEN = 'green';
    #[HexColor('#dd84ff')]
    case PURPLE = 'purple';
    #[HexColor('#ffd084')]
    case ORANGE = 'orange';
    #[HexColor('#ff8484')]
    case RED = 'red';
    #[HexColor('#84ffe0')]
    case MINT = 'mint';
}
