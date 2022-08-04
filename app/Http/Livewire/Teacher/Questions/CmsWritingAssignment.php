<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CmsWritingAssignment extends CmsBase
{
    public $settingsGeneralPropertiesVisibility = [
        'spellingCheckAvailableDuringAssessing' => true,
    ];
    protected $questionOptions = [
        'lang'                  => 'nl_NL',
        'spell_check_available' => true,
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
        $this->questionOptions['lang'] =  Auth::user()->schoolLocation->wsc_language;
        foreach ($this->questionOptions as $key => $value) {
            $this->instance->question[$key] = $value;
        }
    }

    public function initializePropertyBag($q)
    {
        foreach ($this->questionOptions as $key => $val) {
            $this->instance->question[$key] = $q[$key];
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
