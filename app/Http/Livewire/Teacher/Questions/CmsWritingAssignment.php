<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CmsWritingAssignment extends CmsBase
{
    public $settingsGeneralPropertiesVisibility = [
        'spellingCheckAvailableDuringAssessing' => true,
    ];

    public function getTranslationKey(): string
    {
        return __('cms.writing-assignment-question');
    }

    public function getTemplate(): string
    {
        return 'open-question';
    }

    public function preparePropertyBag()
    {
        $questionOptions = [
            'lang' => (Auth::user()->schoolLocation->school_language == 'nl') ? 'nl_NL' : 'en_GB',
        ];
        foreach ($questionOptions as $key => $value) {
            $this->instance->question[$key] = $value;
        }
    }

    public function updated($name, $value)
    {
        if ($name === 'question.subtype') {
            $this->instance->subtype = $value;
        }
    }

    public function updating($name, $value)
    {
        if ($name == 'question.answer' && clean($this->instance->question['answer']) == clean($value)) {
            $this->instance->registerDirty = false;
        }
    }
}
