<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Ramsey\Uuid\Uuid;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\TestQuestion;

class CmsRanking extends CmsBase
{
    CONST MIN_ANSWER_COUNT = 2;

    private $instance;
    public $requiresAnswer = true;

    public function __construct(OpenShort $instance) {
        $this->instance = $instance;
        if ($this->instance->action == 'edit') {
            $this->setAnswerStruct();
        } elseif(!array_key_exists('answerStruct', $this->instance->cmsPropertyBag)) {
            $this->instance->cmsPropertyBag['answerStruct'] = [];
            $this->instance->cmsPropertyBag['answerCount'] = 2;
        }

    }

    public function getTranslationKey() {
        return __('cms.ranking-question');
    }

    public function initializePropertyBag($q)
    {

    }

    public function mergeRules(&$rules)
    {
        $rules += [
            'question.answers'          => 'required|array|min:2',
            'question.answers.*.answer' => 'required',
            'question.answers.*.order'  => 'required',
        ];
    }

    public function updateRankingOrder($value)
    {
        foreach($value as $key => $item){
            $this->instance->cmsPropertyBag['answerStruct'][((int) $item['value'])-1]['order'] = $item['order'];
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
        if(!$this->canDelete()) {
            return;
        }

        $this->instance->cmsPropertyBag['answerStruct'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->filter(function($answer) use ($id){
            return $answer['id'] != $id;
        })->toArray());

        if(self::MIN_ANSWER_COUNT < $this->instance->cmsPropertyBag['answerCount']) {
            $this->instance->cmsPropertyBag['answerCount']--;
        }
        $this->createAnswerStruct();
    }

    public function addAnswerItem()
    {
        $this->instance->cmsPropertyBag['answerCount']++;
        $this->createAnswerStruct();
    }

    public function rankingUpdated($name,$value)
    {
        $this->createAnswerStruct();
    }

    public function createAnswerStruct()
    {
        $result = [];

        collect($this->instance->cmsPropertyBag['answerStruct'])->each(function ($value, $key) use (&$result) {
            $result[] = (object)['id' => $value['id'], 'order' => $key + 1, 'answer' => $value['answer']];
        })->toArray();

        if(count($this->instance->cmsPropertyBag['answerStruct']) < $this->instance->cmsPropertyBag['answerCount']){
            for($i = count($this->instance->cmsPropertyBag['answerStruct']);$i < $this->instance->cmsPropertyBag['answerCount'];$i++){
                $result[] = (object)[
                    'id'    => Uuid::uuid4(),
                    'order' => $i+1,
                    'answer' => ''
                ];
            }
        }

        $this->instance->cmsPropertyBag['answerStruct']  = $result;
        $this->instance->cmsPropertyBag['answerCount'] = count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function prepareForSave()
    {
        $this->instance->question['answers'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->map(function($answer){
            return [
                'order' => $answer['order'],
                'answer' => BaseHelper::transformHtmlChars($answer['answer']),
            ];
        })->toArray());
        unset($this->instance->question['answer']);
    }

    public function arrayCallback($args)
    {
        $this->updateRankingOrder($args);
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

            $this->instance->cmsPropertyBag['answerStruct'] = $q->rankingQuestionAnswers->map(function ($answer, $key) {
                return [
                    'id'     => Uuid::uuid4(),
                    'order'  => $key + 1,
                    'answer' => BaseHelper::transformHtmlCharsReverse($answer->answer),
                ];
            })->toArray();
        }
        $this->instance->cmsPropertyBag['answerCount'] = count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function getTemplate()
    {
        return 'ranking-question';
    }
}
