<?php

namespace tcCore\Http\Enums;

enum AnswerFeedbackFilter: string
{
    case ALL = 'all';
    case STUDENTS = 'students';
    case TEACHERS = 'teachers';
    case CURRENT_USER = 'current_user';
}