<?php

namespace tcCore\Http\Enums;

enum PasswordStatus: string
{
    case NOT_EXPOSED = 'not_exposed';
    case EXPOSED = 'exposed';
    case UNKNOWN = 'unknown';
}