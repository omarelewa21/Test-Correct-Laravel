<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use tcCore\CompletionQuestion;

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

    public function initializePropertyBag($q)
    {
        $this->instance->question['question'] = $this->instance->decodeCompletionTags($q);
    }

    public function customValidation()
    {
        $validator = Validator::make([], []);
        $questionString = $this->instance->question['question'];
        $subType = $this->instance->question['subtype'];
        CompletionQuestion::validateWithValidator($validator, $questionString, $subType, 'question.');
        if ($validator->errors()->count()) {
            throw new ValidationException($validator);
        }
    }
}
