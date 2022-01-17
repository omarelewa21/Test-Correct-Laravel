<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsFactory
{

    public static function create($question, OpenShort $instance)
    {
        if ($question['type'] == 'MultipleChoiceQuestion' && Str::lower($question['subtype']) == 'truefalse') {
            return new CmsTrueFalse($instance);
        }

        if ($question['type'] == 'MultipleChoiceQuestion' && Str::lower($question['subtype']) == 'multiplechoice') {
            return new CmsMultipleChoice($instance);
        }




        return null;
    }
}
