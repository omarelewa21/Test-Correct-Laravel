<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Ramsey\Uuid\Uuid;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Interfaces\QuestionCms;
use tcCore\Http\Traits\WithCmsCustomRulesHandling;
use tcCore\MultipleChoiceQuestion;
use tcCore\Question;
use tcCore\TestQuestion;

class CmsArq
{
    use WithCmsCustomRulesHandling;

    private $instance;
    public $requiresAnswer = true;

    public function __construct(QuestionCms $instance)
    {
        $this->instance = $instance;

        if ($this->instance->action == 'edit') {
            $this->setAnswerStruct();
        } elseif (!array_key_exists('answerStruct', $this->instance->cmsPropertyBag)) {
            $this->instance->cmsPropertyBag['answerStruct'] = [];
        }

    }

    public function showQuestionScore()
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

    public function initializePropertyBag($q)
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


    public function getTranslationKey()
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

    public function getTemplate()
    {
        return 'arq-question';
    }

    /**
     * @return mixed|\tcCore\Question
     */
    private function getQuestion()
    {
        if ($this->instance instanceof OpenShort) {
            if ($this->instance->isPartOfGroupQuestion()) {
                $tq = GroupQuestionQuestion::whereUuid($this->instance->groupQuestionQuestionId)->first();
            } else {
                $tq = TestQuestion::whereUuid($this->instance->testQuestionId)->first();
            }
            return $tq->question;
        }

        return Question::whereUuid($this->instance->question['uuid'])->first();
    }
}
