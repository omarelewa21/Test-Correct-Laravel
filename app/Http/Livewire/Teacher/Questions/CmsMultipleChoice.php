<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Ramsey\Uuid\Uuid;
use tcCore\GroupQuestionQuestion;
use tcCore\TestQuestion;

class CmsMultipleChoice extends CmsBase
{
    const MIN_ANSWER_COUNT = 2;

    private $instance;
//    private $answerCount = 2;
    public $requiresAnswer = true;


    public function __construct(OpenShort $instance)
    {
        $this->instance = $instance;

        if ($this->instance->action == 'edit') {
            $this->setAnswerStruct();
        } elseif(!array_key_exists('answerStruct', $this->instance->cmsPropertyBag)) {
            $this->instance->cmsPropertyBag['answerStruct'] = [];
            $this->instance->cmsPropertyBag['answerCount'] = 2;
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
                'question.answers'          => 'required|array|min:2',
                'question.answers.*.score'  => 'required|integer',
                'question.answers.*.answer' => 'required',
                'question.answers.*.order'  => 'required',
                'question.score'            => 'required|integer|min:1',

            ];
    }

    public function preparePropertyBag()
    {
        $this->createAnswerStruct();


    }

    public function initializePropertyBag($q)
    {

    }

    public function prepareForSave()
    {
        $this->instance->question['answers'] = collect($this->instance->cmsPropertyBag['answerStruct'])
                ->map(function ($answer) {
                    $answer = (array) $answer;
                    return [
                        'order'  => $answer['order'],
                        'answer' => CmsBase::transformHtmlChars($answer['answer']),
                        'score'  => (int)$answer['score'],
                    ];
                })
                ->toArray();
        unset($this->instance->question['answer']);
        $this->instance->question['score'] = collect($this->instance->cmsPropertyBag['answerStruct'])->sum('score');

    }

    public function createAnswerStruct()
    {
        $result = [];

        collect($this->instance->cmsPropertyBag['answerStruct'])->each(function ($value, $key) use (&$result) {
            $value = (array) $value;

            $result[] = (object) [
                'id'    => $value['id'],
                'order' => $key + 1,
                'answer' => $value['answer'],
                'score' => (int) $value['score']
            ];
        })->toArray();

        if (count($this->instance->cmsPropertyBag['answerStruct']) < $this->instance->cmsPropertyBag['answerCount']) {
            for ($i = count($this->instance->cmsPropertyBag['answerStruct']); $i < $this->instance->cmsPropertyBag['answerCount']; $i++) {
                $result[] = (object) [
                    'id'     => Uuid::uuid4(),
                    'order'  => $i + 1,
                    'score'  => 0,
                    'answer' => ''
                ];
            }
        }

        $this->instance->cmsPropertyBag['answerStruct'] = $result;
        $this->instance->cmsPropertyBag['answerCount'] = count($this->instance->cmsPropertyBag['answerStruct']);
    }


    public function getTranslationKey()
    {
        return __('cms.multiplechoice-question-multiplechoice');
    }

    // Multiple Choice
    public function updateMCOrder($value)
    {
        foreach ($value as $item) {
            $this->instance->cmsPropertyBag['answerStruct'][((int) $item['value']) - 1]['order'] = $item['order'];
        }

        $this->instance->cmsPropertyBag['answerStruct'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->sortBy('order')->toArray());
        $this->createAnswerStruct();

    }

    public function canDelete()
    {
        return self::MIN_ANSWER_COUNT < count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function delete($id)
    {

        if (!$this->canDelete()) {
            return;
        }

        $this->instance->cmsPropertyBag['answerStruct'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->filter(function (
            $answer
        ) use ($id) {
            return $answer['id'] != $id;
        })->toArray());

        if (self::MIN_ANSWER_COUNT < $this->instance->cmsPropertyBag['answerCount']) {
            $this->instance->cmsPropertyBag['answerCount']--;
        }
        $this->createAnswerStruct();
    }

    public function addAnswerItem()
    {
        $this->instance->cmsPropertyBag['answerCount']++;
        $this->createAnswerStruct();
    }

    public function updated($prop, $args)
    {
        $this->createAnswerStruct();
    }

    public function arrayCallback($args)
    {
        $this->updateMCOrder($args);
    }

    private function setAnswerStruct()
    {
        if (empty($this->instance->cmsPropertyBag['answerStruct'])) {
            if ($this->instance->isPartOfGroupQuestion()) {
                $tq = GroupQuestionQuestion::whereUuid($this->instance->groupQuestionQuestionId)->first();
                $q = $tq->question;
            } else {
                $tq = TestQuestion::whereUuid($this->instance->testQuestionId)->first();
                $q = $tq->question;
            }

            $this->instance->cmsPropertyBag['answerStruct'] = $q->multipleChoiceQuestionAnswers->map(function ($answer, $key) {
                return [
                    'id'     => Uuid::uuid4(),
                    'order'  => $key + 1,
                    'score'  => $answer->score,
                    'answer' => CmsBase::transformHtmlCharsReverse($answer->answer),
                ];
            })->toArray();
        }
        $this->instance->cmsPropertyBag['answerCount'] = count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function getTemplate()
    {
        return 'multiple-choice-question';
    }
}
