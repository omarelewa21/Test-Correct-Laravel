<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use tcCore\Question;
use tcCore\Attachment;
use tcCore\TestQuestion;
use tcCore\UserFeatureSetting;
use tcCore\GroupQuestionQuestion;
use Illuminate\Support\Facades\Auth;
use tcCore\Http\Interfaces\CmsProvider;
use tcCore\Http\Interfaces\QuestionCms;
use tcCore\Http\Livewire\Teacher\Cms\Constructor;

abstract class TypeProvider implements CmsProvider
{
    protected $instance;
    public $requiresAnswer = true;
    protected $questionOptions = [];

    public function __construct(QuestionCms $instance)
    {
        $this->instance = $instance;
    }

    public function isCarouselGroup()
    {
        return false;
    }

    /**
     * @return mixed|\tcCore\Question
     */
    protected function getQuestion()
    {
        if ($this->instance instanceof Constructor) {
            if ($this->instance->isPartOfGroupQuestion()) {
                $tq = GroupQuestionQuestion::whereUuid($this->instance->groupQuestionQuestionId)->firstOrFail();
            } else {
                $tq = TestQuestion::whereUuid($this->instance->testQuestionId)->firstOrFail();
            }
            return $tq->question;
        }

        return Question::whereUuid($this->instance->question['uuid'])->first();
    }

    public function getVideoHost($link): ?string
    {
        return Attachment::getVideoHost($link);
    }

    public function getTranslationKey(): string
    {
        return __(
            'question.' . str($this->instance->question['type'])->append($this->instance->question['subtype'])->lower()
        );
    }

    public function preparePropertyBag()
    {
        $featureSettings = UserFeatureSetting::getAll(Auth::user());
            foreach ($this->questionOptions as $key => $value) {    
                if ($key == 'max_words')
                    $this->instance->question['max_words'] = $featureSettings['max_words_default'] ?? null;
                elseif($key == 'spell_check_available' && !settings()->canUseCmsWscWriteDownToggle())
                    $this->instance->question['spell_check_available'] = false;
                elseif(isset($featureSettings[$key. '_default']))
                    $this->instance->question[$key] = $featureSettings[$key . '_default'] ? true : false;
                else
                    $this->instance->question[$key] = $value;
            }
    }

    public function initializePropertyBag($q)
    {
        foreach($this->questionOptions as $key => $val){
            $this->instance->question[$key] = $q[$key];
        }
    }
}