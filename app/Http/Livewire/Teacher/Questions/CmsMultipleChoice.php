<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Ramsey\Uuid\Uuid;
use tcCore\GroupQuestionQuestion;
use tcCore\TestQuestion;

class CmsMultipleChoice
{
    CONST MIN_ANSWER_COUNT = 2;

    private $instance;
    private $answerCount = 2;

    public function __construct(OpenShort $instance) {
        $this->instance = $instance;
        $this->setAnswerStruct();
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

    public function initializePropertyBag($q)
    {
    }

    public function prepareForSave()
    {
        $this->instance->question['answers'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->map(function($answer){
            return [
                'order' => $answer['order'],
                'answer' => $answer['answer'],
                'score' => $answer['score'],
            ];
        })->toArray());
        unset($this->instance->question['answer']);
        $this->instance->question['score'] = collect($this->instance->cmsPropertyBag['answerStruct'])->sum('score');

    }

    public function createMCAnswerStruct()
    {
        $result = [];

        collect($this->instance->cmsPropertyBag['answerStruct'])->each(function ($value, $key) use (&$result) {
            $result[] = (object)['id' => $value['id'], 'order' => $key + 1, 'answer' => $value['answer'], 'score' => (int) $value['score']];
        })->toArray();

        if(count($this->instance->cmsPropertyBag['answerStruct']) < $this->answerCount){
            for($i = count($this->instance->cmsPropertyBag['answerStruct']);$i < $this->answerCount; $i++){
                $result[] = (object)[
                    'id'    => Uuid::uuid4(),
                    'order' => $i+1,
                    'score' => 0,
                    'answer' => ''
                ];
            }
        }

        $this->instance->cmsPropertyBag['answerStruct']  = $result;
        $this->answerCount = count($this->instance->cmsPropertyBag['answerStruct']);
    }


    public function getTranslationKey() {
        return 'cms.multiplechoice-question-multiplechoice';
    }

    // Multiple Choice
    public function updateMCOrder($value)
    {
        foreach($value as $item){
            $this->instance->cmsPropertyBag['answerStruct'][((int) $item['value'])-1]['order'] = $item['order'];
        }

        $this->instance->cmsPropertyBag['answerStruct'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->sortBy('order')->toArray());
        $this->createMCAnswerStruct();

    }

    public function mcCanDelete()
    {
        return self::MIN_ANSWER_COUNT < count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function mcDelete($id)
    {

        if(!$this->mcCanDelete()) {
            return;
        }

        $this->instance->cmsPropertyBag['answerStruct'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->filter(function($answer) use ($id){
            return $answer['id'] != $id;
        })->toArray());

        if(self::MIN_ANSWER_COUNT < $this->answerCount) {
            $this->answerCount--;
        }
        $this->createMCAnswerStruct();
    }

    public function mcAddAnswerItem()
    {
        $this->answerCount++;
        $this->createMCAnswerStruct();
    }

    public function mcUpdated($args)
    {
        $this->createMCAnswerStruct();
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
                    'answer' => $answer->answer,
                ];
            })->toArray();
        }

        $this->answerCount = count($this->instance->cmsPropertyBag['answerStruct']);
    }
}
