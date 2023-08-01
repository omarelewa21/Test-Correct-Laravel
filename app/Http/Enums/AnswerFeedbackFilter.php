<?php

namespace tcCore\Http\Enums;

enum AnswerFeedbackFilter: string
{
    case ALL = 'all';
    case STUDENTS = 'students';
    case TEACHER = 'teacher';
    case CURRENT_USER = 'current_user';
}