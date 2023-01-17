<?php

namespace tcCore\Http\Enums\CoLearning;

enum ScoringStatus: string
{
    case Green  = 'green';
    case Orange = 'orange';
    case Red    = 'red';
    case Grey   = 'grey';
}
