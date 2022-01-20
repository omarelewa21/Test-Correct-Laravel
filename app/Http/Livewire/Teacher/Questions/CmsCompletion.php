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

    protected $questionOptions = [
        'auto_check_answer' => false,
        'auto_check_answer_case_sensitive' => false,
    ];

    public $settingsGeneralPropertiesVisibility = [
        'autoCheckAnswer' => true,
        'autoCheckAnswerCaseSensitive' => true,
    ];

    public function __construct(OpenShort $instance)
    {
        $this->instance = $instance;
    }

    public function preparePropertyBag()
    {
        foreach ($this->questionOptions as $key => $value) {
            $this->instance->question[$key] = $value;
        }
    }

    public function initializePropertyBag($q)
    {
        foreach($this->questionOptions as $key => $val){
            $this->instance->question[$key] = $q[$key];
        }
    }

    public function isSettingsGeneralPropertyDisabled($property, $asText = false)
    {
        if ($property === 'autoCheckAnswerCaseSensitive') {
            if (!$this->instance->question['auto_check_answer']) {
                return true;
            }
        }

        return false;
    }

    public function updated($val)
    {
        if(!$this->instance->question['auto_check_answer']){
            $this->instance->question['auto_check_answer_case_sensitive'] = false;
        }
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
        $validator = Validator::make([], []);
        $questionString = $this->instance->question['question'];
        $subType = $this->instance->question['subtype'];
        CompletionQuestion::validateWithValidator($validator, $questionString, $subType, 'question.');
        if ($validator->errors()->count()) {
            throw new ValidationException($validator);
        }
    }
}
