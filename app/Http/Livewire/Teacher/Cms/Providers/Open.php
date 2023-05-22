<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use tcCore\CompletionQuestion;

class Open extends TypeProvider
{
    protected $questionOptions = [
        'spell_check_available' => false,
        'text_formatting'       => false,
        'mathml_functions'      => false,
        'restrict_word_amount'  => false,
        'max_words'             => null,
    ];

    public function getTemplate(): string
    {
        return 'open-question';
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
}
