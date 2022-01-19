<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsCompletion
{
    private $instance;
    public $requiresAnswer = false;

    public function __construct(OpenShort $instance)
    {
        $this->instance = $instance;
    }

    public function getTranslationKey()
    {
        return __('cms.completion-question');
    }

    public function getTemplate()
    {
        return 'completion-question';
    }
}
