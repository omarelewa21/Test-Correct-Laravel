<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsGroup
{
    private $instance;
    public $requiresAnswer = false;


    public function __construct(OpenShort $instance) {
        $this->instance = $instance;
    }

    public function getTranslationKey() {
        return __('cms.group-question');
    }

    public function getTemplate()
    {
        return 'group-question';
    }
}
