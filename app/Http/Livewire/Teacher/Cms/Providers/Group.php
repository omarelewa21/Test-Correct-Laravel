<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use tcCore\GroupQuestion;

class Group extends TypeProvider
{
    public $requiresAnswer = false;

    private $questionProperties = [
        'name'                   => '',
        'groupquestion_type'     => 'standard',
        'number_of_subquestions' => 1,
        'uuid'                   => ''
    ];

    public function mergeRules(&$rules)
    {
        $rules = [
            'question.name' => 'required',
        ];
    }

    public function getTranslationKey(): string
    {
        return __('cms.group-question');
    }

    public function getTemplate(): string
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
        $this->instance->question['name'] = html_entity_decode($this->instance->question['name']);
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
        if (!$this->instance->question['uuid']) {
            return true;
        }
        return GroupQuestion::whereUuid($this->instance->question['uuid'])->first()->hasEqualScoresForSubQuestions();
    }

    public function hasEnoughSubQuestionsAsCarousel()
    {
        if (!$this->instance->question['uuid']) {
            return true;
        }
        return GroupQuestion::whereUuid($this->instance->question['uuid'])->first()->hasEnoughSubQuestionsAsCarousel();
    }

    public function showQuestionScore()
    {
        return false;
    }

    public function updatedQuestionName($value)
    {
        $event = filled($value) ? 'group-question-name-filled' : 'group-question-name-empty';
        $this->instance->dispatchBrowserEvent($event);
    }

    public function questionSectionTitle(): string
    {
        return __('cms.bijlagen');
    }

    public function isSettingVisible(string $property): bool
    {
        return !in_array(
            $property,
            [
                'discuss',
                'allowNotes',
                'decimalScore',
                'autoCheckAnswer',
                'autoCheckAnswerCaseSensitive',
            ]
        );
    }
}
