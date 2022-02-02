<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Ramsey\Uuid\Uuid;
use tcCore\GroupQuestionQuestion;
use tcCore\TestQuestion;

class CmsClassify extends CmsBase
{
    CONST MIN_ANSWER_COUNT = 2;
    CONST MIN_ANSWER_SUB_COUNT = 1;

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
        return __('cms.classify-question');
    }

    public function initializePropertyBag($q)
    {

    }

    public function mergeRules(&$rules)
    {
        $rules += [
            'question.answers'          => 'required|array|min:2',
            'question.answers.*.left' => 'required',
            'question.answers.*.right' => 'required',
            'question.answers.*.order'  => 'required',
        ];
    }

    public function updateRankingOrder($value)
    {dd($value);
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

    public function canDeleteSubItem($key)
    {
        return self::MIN_ANSWER_SUB_COUNT < count($this->instance->cmsPropertyBag['answerStruct'][$key]);
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

    public function deleteSubItem($key, $id)
    {
        if(!$this->canDeleteSubItem($key)) {
            return;
        }

        $this->instance->cmsPropertyBag['answerStruct'][$key] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'][$key])->filter(function($answer) use ($id){
            return $answer['id'] != $id;
        })->toArray());

        if(self::MIN_ANSWER_SUB_COUNT < $this->instance->cmsPropertyBag[$key]['answerCount']) {
            $this->instance->cmsPropertyBag['answerCount']--;
        }
        $this->createAnswerStruct();
    }

    public function addAnswerItem($key)
    {
        $this->instance->cmsPropertyBag[$key]['answerCount']++;
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
            $result[] = (object)['id' => $value['id'], 'order' => $key + 1, 'left' => $value['left'], 'right' => $value['right']];
        })->toArray();

        if(count($this->instance->cmsPropertyBag['answerStruct']) < $this->instance->cmsPropertyBag['answerCount']){
            for($i = count($this->instance->cmsPropertyBag['answerStruct']);$i < $this->instance->cmsPropertyBag['answerCount'];$i++){
                $result[] = (object)[
                    'id'    => Uuid::uuid4(),
                    'order' => $i+1,
                    'left' => '',
                    'right' => '',
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
                'left' => $this->transformHtmlChars($answer['left']),
                'right' => $this->transformHtmlChars($answer['right']),
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
            $corresponding = (object) [
                'id' => '',
                'answer' => '',
                'type' => ''
            ];
            $this->instance->cmsPropertyBag['answerStruct'] = $q->matchingQuestionAnswers->map(function ($answer, $key) use (&$corresponding) {
                if($answer->type === 'LEFT'){
                    $corresponding = (object) [
                      'id' => $answer->id,
                      'answer' => $answer->answer,
                    ];
                    return null;
                }

                else if($answer->correct_answer_id === $corresponding->id){
                    return [
                        'id'     => Uuid::uuid4(),
                        'order'  => $key + 1,
                        'left' => $this->transformHtmlCharsReverse($corresponding->answer),
                        'right' => $this->transformHtmlCharsReverse($answer->answer),
                    ];
                }

                else {
                    throw new \Exception('Mismatch in the answer details, get in contact with the Test-Correct Helpdesk and notify them about this error with question ID '.$this->instance->question['id']);
                }
            })->filter(function($answer, $key) { return $answer != null; })->toArray();
            $this->instance->cmsPropertyBag['answerStruct'] = array_values($this->instance->cmsPropertyBag['answerStruct']);
        }
        $this->instance->cmsPropertyBag['answerCount'] = count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function getTemplate()
    {
        return 'matching-question';
    }
}
