<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

class CmsFactory
{

    public static function create($question, OpenShort $instance)
    {
        if ($question['type'] == 'MultipleChoiceQuestion' && $question['subtype'] == 'TrueFalse') {
            return new CmsTrueFalse($instance);
        }

        if ($question['type'] == 'MultipleChoiceQuestion' && $question['subtype'] == 'multiplechoice') {
            return new CmsMultipleChoice($instance);
        }




        return null;
    }
}
