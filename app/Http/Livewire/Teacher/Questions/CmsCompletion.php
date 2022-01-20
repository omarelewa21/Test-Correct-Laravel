<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use tcCore\CompletionQuestion;

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

    public function customValidation()
    {
        $validator = Validator::make([],[]);
        $questionString = $this->instance->question['question'];
        $subType = $this->instance->question['subtype'];
        CompletionQuestion::validateWithValidator($validator,$questionString,$subType,'question.');
        if($validator->errors()){
            throw new ValidationException($validator);
        }
    }
}
