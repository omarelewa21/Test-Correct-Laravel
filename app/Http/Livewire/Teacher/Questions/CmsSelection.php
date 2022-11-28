<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use tcCore\CompletionQuestion;
use tcCore\Http\Traits\WithCmsCompletionType;

class CmsSelection extends CmsBase
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

    public function initializePropertyBag($q)
    {
        $this->instance->question['question'] = CompletionQuestion::decodeCompletionTags($q);
    }
}
