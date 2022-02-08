<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Ramsey\Uuid\Uuid;
use tcCore\GroupQuestionQuestion;
use tcCore\TestQuestion;

class CmsClassify extends CmsBase
{
    const MIN_ANSWER_COUNT = 2;
    const MIN_ANSWER_SUB_COUNT = 1;

    private $instance;
    public $requiresAnswer = true;

    public function __construct(OpenShort $instance)
    {
        $this->instance = $instance;
        if ($this->instance->action == 'edit') {
            $this->setAnswerStruct();
        } elseif (!array_key_exists('answerStruct', $this->instance->cmsPropertyBag)) {
            $this->instance->cmsPropertyBag['answerStruct'] = [];
            $this->instance->cmsPropertyBag['answerCount'] = 2;
            $this->instance->cmsPropertyBag['answerSubCount'] = [];
        }

    }

    public function getTranslationKey()
    {
        return __('cms.classify-question');
    }

    public function initializePropertyBag($q)
    {
        if (!array_key_exists('answerSubCount', $this->instance->cmsPropertyBag)) {
            $this->instance->cmsPropertyBag['answerSubCount'] = [];
        }
    }

    public function mergeRules(&$rules)
    {
        $rules += [
            'question.answers' => 'required|array|min:2',
            'question.answers.*.left' => 'required',
            'question.answers.*.right' => 'required',
            'question.answers.*.order' => 'required',
        ];
    }

    public function updateRankingOrder($value)
    {
        foreach ($value as $item) {
            list($key, $id) = explode('=',$item['value']);
            $this->instance->cmsPropertyBag['answerStruct'][$key]['rights'] = collect($this->instance->cmsPropertyBag['answerStruct'][$key]['rights'])->map(function($answer) use ($item, $id){
              if($answer['id'] == $id){
                  $answer['order'] = $item['order'];
              }
              return $answer;
            })->toArray();
        }
        $this->instance->cmsPropertyBag['answerStruct'][$key]['rights'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'][$key]['rights'])->sortBy('order')->toArray());
        $this->createAnswerStruct();

    }

    public function canDelete()
    {
        return self::MIN_ANSWER_COUNT < count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function canDeleteSubItem($key)
    {
        $obj = (object) $this->instance->cmsPropertyBag['answerStruct'][$key];
        return self::MIN_ANSWER_SUB_COUNT < count($obj->rights);
    }

    public function delete($id)
    {
        if (!$this->canDelete()) {
            return;
        }

        $this->instance->cmsPropertyBag['answerStruct'] = collect($this->instance->cmsPropertyBag['answerStruct'])->filter(function ($answer) use ($id) {
            return $answer['id'] != $id;
        })->toArray();

        if (self::MIN_ANSWER_COUNT < $this->instance->cmsPropertyBag['answerCount']) {
            $this->instance->cmsPropertyBag['answerCount']--;
        }
        $this->createAnswerStruct();
    }

    public function deleteSubItem($keyId)
    {
        list($key, $id) = explode('=',$keyId);

        if (!$this->canDeleteSubItem($key)) {
            return;
        }

        $this->instance->cmsPropertyBag['answerStruct'][$key]['rights'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'][$key]['rights'])->filter(function ($answer) use ($id) {
            return $answer['id'] != $id;
        })->toArray());

        if (self::MIN_ANSWER_SUB_COUNT < $this->instance->cmsPropertyBag['answerSubCount'][$key]) {
            $this->instance->cmsPropertyBag['answerSubCount'][$key]--;
        }
        $this->createAnswerStruct();
    }

    public function addAnswerItem()
    {
        $this->instance->cmsPropertyBag['answerCount']++;
        $this->createAnswerStruct();
    }

    public function addAnswerSubItem($key)
    {
        $this->instance->cmsPropertyBag['answerSubCount'][$key]++;
        $this->createAnswerStruct();
    }

    public function rankingUpdated($name, $value)
    {
        $this->createAnswerStruct();
    }

    public function createAnswerStruct()
    {
        $result = [];
        $nr = 0;
        foreach ($this->instance->cmsPropertyBag['answerStruct'] as $key => $value) {
            $result[$key] = (object)['id' => $key, 'order' => $nr + 1, 'left' => $value['left'], 'rights' => $value['rights']];
            if (!isset($this->instance->cmsPropertyBag['answerSubCount'][$key])) {
                $this->instance->cmsPropertyBag['answerSubCount'][$key] = 1;
            }
            $ref = $this->instance->cmsPropertyBag['answerSubCount'][$key];
            if (count($value['rights']) < $ref) {
                for ($i = count($value['rights']); $i < $ref; $i++) {
                    $uuidSub = Uuid::uuid4()->toString();
                    $result[$key]->rights[] = [
                        'id' => $uuidSub,
                        'order' => $i+1,
                        'answer' => ''
                    ];
                }
            }
            $nr++;
        }

        if (count($this->instance->cmsPropertyBag['answerStruct']) < $this->instance->cmsPropertyBag['answerCount']) {
            for ($i = count($this->instance->cmsPropertyBag['answerStruct']); $i < $this->instance->cmsPropertyBag['answerCount']; $i++) {
                $key = Uuid::uuid4()->toString();
                $uuidSub = Uuid::uuid4()->toString();
                $result[$key] = (object)[
                    'id' => $key,
                    'order' => $i + 1,
                    'left' => '',
                    'rights' => [
                        [
                            'id' => $uuidSub,
                            'order' => 1,
                            'answer' => ''
                        ]
                    ],
                ];

                $this->instance->cmsPropertyBag['answerSubCount'][$key] = 1;
            }
        }

        $this->instance->cmsPropertyBag['answerStruct'] = $result;
        $this->instance->cmsPropertyBag['answerCount'] = count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function prepareForSave()
    {
        $this->instance->question['answers'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->map(function ($answer) {
            $rights = collect($answer['rights'])->map(function($ar){
               return trim($ar['answer']);
            })->filter(function($answer){
                return $answer === '';
            })->toArray();
            return [
                'order' => $answer['order'],
                'left' => $this->transformHtmlChars($answer['left']),
                'right' => $this->transformHtmlChars(implode(PHP_EOL,$rights)),
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

            $struct = [];

            $corresponding = null;

            $q->matchingQuestionAnswers->each(function($answer) use (&$corresponding, &$struct){
                if($answer->type === 'LEFT'){
                    if($corresponding){
                        $struct[$corresponding['id']] = $corresponding;
                        $this->instance->cmsPropertyBag['answerSubCount'][$corresponding['id']] = count($corresponding['rights']);
                        $corresponding = null;
                    }
                    $corresponding = [
                        'id' => Uuid::uuid4()->toString(),
                        'left' => $answer->answer,
                        'order' => count($struct) + 1,
                        'rights' => [],
                    ];
                } else {
                    $corresponding['rights'][] = [
                        'id' => Uuid::uuid4()->toString(),
                        'order' => count($corresponding['rights']) + 1,
                        'answer' => $answer->answer,
                    ];
                }
            });
            if($corresponding){
                $struct[$corresponding['id']] = $corresponding;
                $this->instance->cmsPropertyBag['answerSubCount'][$corresponding['id']] = count($corresponding['rights']);
            }

            $this->instance->cmsPropertyBag['answerStruct'] = $struct;
        }
        $this->instance->cmsPropertyBag['answerCount'] = count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function getTemplate()
    {
        return 'classify-question';
    }
}
