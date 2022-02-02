<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsFactory
{

    private static $self;

    public static function create(OpenShort $instance)
    {
        if (static::$self) {
            return static::$self;
        }

        if ($instance->question['type'] == 'CompletionQuestion') {
            if (Str::lower($instance->question['subtype']) == 'multi') {
               static::$self = new CmsSelection($instance);
            }
            if (Str::lower($instance->question['subtype']) == 'completion') {
                static::$self = new CmsCompletion($instance);
            }
        }

        if ($instance->question['type'] == 'MultipleChoiceQuestion' && Str::lower($instance->question['subtype']) == 'truefalse') {
            static::$self =  new CmsTrueFalse($instance);
        }

        if ($instance->question['type'] == 'MultipleChoiceQuestion' && Str::lower($instance->question['subtype']) == 'multiplechoice') {
            static::$self =new CmsMultipleChoice($instance);
        }

        if ($instance->question['type'] == 'MultipleChoiceQuestion' && Str::lower($instance->question['subtype']) == 'arq') {
            static::$self =new CmsArq($instance);
        }

        if ($instance->question['type'] == 'InfoscreenQuestion') {
            static::$self = new CmsInfoScreen($instance);
        }

        if ($instance->question['type'] == 'RankingQuestion') {
            static::$self =  new CmsRanking($instance);
        }

        if ($instance->question['type'] == 'OpenQuestion') {
            static::$self =  new CmsOpen($instance);
        }

        return static::$self ;
    }
}
