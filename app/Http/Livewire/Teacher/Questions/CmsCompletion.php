<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use tcCore\Http\Traits\WithCmsCompletionType;

class CmsCompletion
{
    use WithCmsCompletionType;

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

        $this->instance->question['question'] = $this->instance->decodeCompletionTags($q);
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

    public function updated($prop, $val)
    {
        if($prop === 'question.auto_check_answer' && $this->instance->question['auto_check_answer']){
            $this->instance->question['auto_check_answer_case_sensitive'] = true;
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
}
