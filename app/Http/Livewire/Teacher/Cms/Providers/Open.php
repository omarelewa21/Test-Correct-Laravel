<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use tcCore\UserFeatureSetting;
use Illuminate\Support\Facades\Auth;
use tcCore\Http\Interfaces\QuestionCms;

class Open extends TypeProvider
{
    protected $questionOptions = [
        'spell_check_available' => false,
        'text_formatting'       => false,
        'mathml_functions'      => false,
        'restrict_word_amount'  => false,
        'max_words'             => null,
    ];

    public function __construct(QuestionCms $instance)
    {
        parent::__construct($instance);
        $this->preparePropertyBag();
    }

    public function getTemplate(): string
    {
        return 'open-question';
    }

    public function updatedQuestionRestrictWordAmount(bool $value)
    {
        if ($value && !$this->instance->question['max_words']) {
            $this->instance->question['max_words'] = 50;
        }
    }

    public function updated($name, $value)
    {
        if ($name === 'question.subtype') {
            $this->instance->subtype = $value;
        }
    }

    public function updating($name, $value)
    {
        if ($name == 'question.answer' && clean($this->instance->question['answer']) == clean($value)) {
            $this->instance->registerDirty = false;
        }
    }

    public function preparePropertyBag()
    {
        $featureSettings = UserFeatureSetting::getAll(Auth::user());
        foreach ($this->questionOptions as $key => $value) {
            if ($key == 'max_words') {
                $this->instance->question['max_words'] = $featureSettings['max_words_default'] ?? $this->instance->question['max_words'];
            } elseif ($key == 'spell_check_available' && !settings()->canUseCmsWscWriteDownToggle()) {
                $this->instance->question['spell_check_available'] = false;
            } elseif (isset($featureSettings[$key . '_default']) && !isset($featureSettings[$key])) {
                $this->instance->question[$key] = $featureSettings[$key . '_default'] ? true : false;
            } else {
                $this->instance->question[$key] = $value;
            }
        }
    }
}
