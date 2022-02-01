<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsDrawing
{
    private $instance;
    public $requiresAnswer = true;

    public function __construct(OpenShort $instance) {
        $this->instance = $instance;
    }

    public function getTranslationKey() {
        return __('cms.drawing-question');
    }

    public function getTemplate()
    {
        return 'drawing-question';
    }
}
