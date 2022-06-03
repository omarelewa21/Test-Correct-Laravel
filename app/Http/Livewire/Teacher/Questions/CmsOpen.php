<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsOpen
{
    private $instance;
    public $requiresAnswer = true;

    public function __construct(OpenShort $instance) {
        $this->instance = $instance;
    }

    public function getTranslationKey() {
        if (Str::lower($this->instance->question['subtype']) == 'short') {
            return __('cms.open-question-short');
        }
        return __('cms.open-question-medium');
    }

    public function getTemplate()
    {
        return 'open-question';
    }

    public function updated($name, $value)
    {
        if ($name === 'question.subtype') {
            $this->instance->subtype = $value;
        }
    }

    public function updating($name, $value)
    {
        if ($name == 'question.answer' && html_entity_decode($this->instance->question['answer']) == html_entity_decode($value)) {
            $this->instance->registerDirty = false;
        }
    }
}
