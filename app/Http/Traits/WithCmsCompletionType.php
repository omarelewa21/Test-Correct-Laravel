<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use tcCore\CompletionQuestion;

trait WithCmsCompletionType
{
    public function customValidation()
    {
        $validator = $this->validateOnQuestion();
        if ($validator->errors()->count()) {
            throw new ValidationException($validator);
        }
    }

    public function passesCustomMandatoryRules()
    {
        return !(!!$this->validateOnQuestion()->errors()->count());
    }

    protected function validateOnQuestion()
    {
        $validator = Validator::make([], []);
        $questionString = $this->instance->question['question'];
        $subType = $this->instance->question['subtype'];
        CompletionQuestion::validateWithValidator($validator, $questionString, $subType, 'question.');
        return $validator;
    }
}