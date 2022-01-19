<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsSelection
{
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
}
