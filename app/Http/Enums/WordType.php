<?php

namespace tcCore\Http\Enums;

enum WordType: string
{
    case SUBJECT = 'subject';
    case TRANSLATION = 'translation';
    case DEFINITION = 'definition';
    case SYNONYM = 'synonym';

}
