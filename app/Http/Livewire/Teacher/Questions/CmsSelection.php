<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use tcCore\Http\Traits\WithCmsCompletionType;

class CmsSelection
{
    use WithCmsCompletionType;

    private $instance;
    public $requiresAnswer = false;

    public function __construct(OpenShort $instance)
    {
        $this->instance = $instance;
    }

    public function getTranslationKey()
    {
        return __('cms.selection-question');
    }

    public function getTemplate()
    {
        return 'selection-question';
    }

    public function initializePropertyBag($q)
    {
        $this->instance->question['question'] = $this->instance->decodeCompletionTags($q);
    }
}
