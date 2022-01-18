<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsFactory
{

    public static function create(OpenShort $instance)
    {
        if ($instance->question['type'] == 'MultipleChoiceQuestion' && Str::lower($instance->question['subtype']) == 'truefalse') {
            return new CmsTrueFalse($instance);
        }

        if ($instance->question['type'] == 'MultipleChoiceQuestion' && Str::lower($instance->question['subtype']) == 'multiplechoice') {
            return new CmsMultipleChoice($instance);
        }

        if ($instance->question['type'] == 'InfoscreenQuestion') {
            return new CmsInfoScreen($instance);
        }

        if ($instance->question['type'] == 'RankingQuestion') {
            return new CmsRanking($instance);
        }

        if ($instance->question['type'] == 'OpenQuestion') {
            return new CmsOpen($instance);
        }

        return null;
    }
}
