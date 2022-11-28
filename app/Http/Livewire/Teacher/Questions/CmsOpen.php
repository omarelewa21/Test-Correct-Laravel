<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsOpen extends CmsBase
{
    public function getTranslationKey(): string
    {
        if (Str::lower($this->instance->question['subtype']) == 'short') {
            return __('cms.open-question-short');
        }
        return __('cms.open-question-medium');
    }

    public function getTemplate(): string
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
        if ($name == 'question.answer' && clean($this->instance->question['answer']) == clean($value)) {
            $this->instance->registerDirty = false;
        }
    }
}
