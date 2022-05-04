<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;
use tcCore\GroupQuestion;

class CmsGroup
{
    private $instance;

    public $requiresAnswer = false;

    private $questionProperties = [
        'name'                   => '',
        'groupquestion_type'     => 'standard',
        'number_of_subquestions' => 1,
        'uuid'                   => ''
    ];

    public $settingsGeneralPropertiesVisibility = [
        'autoCheckAnswer'              => false,
        'autoCheckAnswerCaseSensitive' => false,
        'closeable'                    => true,
        'shuffle'                      => true,
        'addToDatabase'                => true,
        'maintainPosition'             => true,
        'discuss'                      => false,
        'allowNotes'                   => false,
        'decimalScore'                 => false,
    ];

    public function mergeRules(&$rules)
    {
        $rules = [
            'question.name' => 'required',
        ];
    }


    public function __construct(OpenShort $instance)
    {
        $this->instance = $instance;
    }

    public function getTranslationKey()
    {
        return __('cms.group-question');
    }

    public function getTemplate()
    {
        return 'group-question';
    }

    public function preparePropertyBag()
    {
        foreach ($this->questionProperties as $key => $value) {
            $this->instance->question[$key] = $value;
        }
    }

    public function initializePropertyBag($q)
    {
        foreach ($this->questionProperties as $key => $val) {
            $this->instance->question[$key] = $q[$key];
        }

        if ($this->instance->question['number_of_subquestions'] == null) {
            $this->instance->question['number_of_subquestions'] = 0;
        }
    }

    public function isCarouselGroup()
    {
        return $this->instance->question['groupquestion_type'] === 'carousel';
    }

    public function hasEqualScoresForSubQuestions()
    {
        return GroupQuestion::whereUuid($this->instance->question['uuid'])->first()->hasEqualScoresForSubQuestions();
    }
    public function hasEnoughSubQuestionsAsCarousel()
    {
        return GroupQuestion::whereUuid($this->instance->question['uuid'])->first()->hasEnoughSubQuestionsAsCarousel();
    }

    public function showQuestionScore()
    {
        return false;
    }
}
