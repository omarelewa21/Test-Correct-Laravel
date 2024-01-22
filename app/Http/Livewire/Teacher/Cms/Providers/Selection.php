<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use tcCore\CompletionQuestion;
use tcCore\Http\Traits\WithCmsCompletionType;

class Selection extends TypeProvider
{
    use WithCmsCompletionType;

    public $requiresAnswer = false;

    public function getTranslationKey(): string
    {
        return __('cms.selection-question');
    }

    public function getTemplate(): string
    {
        return 'selection-question';
    }

    public function initializePropertyBag($question)
    {
        $this->instance->question['question'] = CompletionQuestion::decodeCompletionTags($question);
    }
}
