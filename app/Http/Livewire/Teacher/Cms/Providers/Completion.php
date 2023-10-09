<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use tcCore\CompletionQuestion;
use tcCore\Http\Traits\WithCmsCompletionType;

class Completion extends TypeProvider
{
    use WithCmsCompletionType;

    public $requiresAnswer = false;

    protected $questionOptions = [
        'auto_check_answer' => false,
        'auto_check_answer_case_sensitive' => false,
    ];

    public function preparePropertyBag()
    {
        foreach ($this->questionOptions as $key => $value) {
            if (!array_key_exists($key, $this->instance->question)) {
                $this->instance->question[$key] = $value;
            }
        }
    }

    public function initializePropertyBag($q)
    {
        parent::initializePropertyBag($q);

        $this->instance->question['question'] = CompletionQuestion::decodeCompletionTags($q);
    }

    public function isSettingsGeneralPropertyDisabled($property): bool
    {
        if ($property === 'autoCheckAnswerCaseSensitive' && !$this->instance->question['auto_check_answer']) {
            return true;
        }

        return false;
    }

    public function updated($prop, $val)
    {
        if($prop === 'question.auto_check_answer' && $this->instance->question['auto_check_answer']){
            $this->instance->question['auto_check_answer_case_sensitive'] = true;
        }
    }

    public function getTranslationKey(): string
    {
        return __('cms.completion-question');
    }

    public function getTemplate(): string
    {
        return 'completion-question';
    }

    public function isSettingVisible(string $property): bool
    {
        return true;
    }
}
