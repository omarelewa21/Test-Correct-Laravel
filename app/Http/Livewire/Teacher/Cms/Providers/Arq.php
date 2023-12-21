<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use Ramsey\Uuid\Uuid;
use tcCore\Http\Interfaces\QuestionCms;
use tcCore\Http\Traits\WithCmsCustomRulesHandling;
use tcCore\MultipleChoiceQuestion;

class Arq extends TypeProvider
{
    use WithCmsCustomRulesHandling;

    public function __construct(QuestionCms $instance)
    {
        parent::__construct($instance);

        if ($this->instance->action == 'edit') {
            $this->setAnswerStruct();
        } elseif (!array_key_exists('answerStruct', $this->instance->cmsPropertyBag)) {
            $this->instance->cmsPropertyBag['answerStruct'] = [];
        }

    }

    public function showQuestionScore(): bool
    {
        return false;
    }

    public function mergeRules(&$rules)
    {
        $rules +=
            [
                'question.answers'         => 'required|array|min:5|max:5',
                'question.answers.*.score' => 'required|integer',
                'question.score'           => 'required|integer|min:1',

            ];

    }

    public function preparePropertyBag()
    {
        $this->instance->cmsPropertyBag['arqStructure'] = MultipleChoiceQuestion::getArqStructure();
        $this->createAnswerStruct();
    }

    public function initializePropertyBag($question)
    {

    }

    public function prepareForSave()
    {
        $this->instance->question['answers'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->map(function ($answer) {
            return [
                'answer' => '',
                'score'  => (string)$answer['score'], // needs to be a string in order to validate and be saved
            ];
        })->toArray());

        unset($this->instance->question['answer']);
        $this->instance->question['score'] = collect($this->instance->cmsPropertyBag['answerStruct'])->max('score');
    }

    public function createAnswerStruct()
    {
        $result = [];

        if (count($this->instance->cmsPropertyBag['answerStruct'])) {
            collect($this->instance->cmsPropertyBag['answerStruct'])->each(function ($value, $key) use (&$result) {
                $value = (array)$value;

                $result[] = [
                    'id'     => $value['id'],
                    'answer' => '',
                    'score'  => (int)$value['score']
                ];
            })->toArray();
        } else {

            for ($i = 0; $i < 5; $i++) {
                $result[] = [
                    'id'     => Uuid::uuid4(),
                    'score'  => 0,
                    'answer' => ''
                ];
            }
        }

        $this->instance->cmsPropertyBag['answerStruct'] = $result;
    }


    public function getTranslationKey(): string
    {
        return __('cms.multiplechoice-question-arq');
    }

    public function updated($prop, $args)
    {
        $this->createAnswerStruct();
    }

    private function setAnswerStruct()
    {
        if (empty($this->instance->cmsPropertyBag['answerStruct'])) {
            $q = $this->getQuestion();

            $this->instance->cmsPropertyBag['answerStruct'] = $q->multipleChoiceQuestionAnswers->map(function ($answer, $key) {
                return [
                    'id'     => Uuid::uuid4(),
                    'score'  => $answer->score,
                    'answer' => '',
                ];
            })->toArray();
        }
    }

    public function getTemplate(): string
    {
        return 'arq-question';
    }
}
