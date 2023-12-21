<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Interfaces\QuestionCms;
use tcCore\Http\Traits\WithCmsCustomRulesHandling;

class Matching extends TypeProvider
{
    use WithCmsCustomRulesHandling;

    const MIN_ANSWER_COUNT = 2;

    public function __construct(QuestionCms $instance)
    {
        parent::__construct($instance);

        if ($this->instance->action == 'edit') {
            $this->setAnswerStruct();
        } elseif (!array_key_exists('answerStruct', $this->instance->cmsPropertyBag)) {
            $this->instance->cmsPropertyBag['answerStruct'] = [];
            $this->instance->cmsPropertyBag['answerCount'] = 2;
        }

    }

    public function getTranslationKey(): string
    {
        return __('cms.matching-question');
    }

    public function initializePropertyBag($question)
    {

    }

    public function mergeRules(&$rules)
    {
        $rules += [
            'question.answers'         => 'required|array|min:2',
            'question.answers.*.left'  => 'required',
            'question.answers.*.right' => 'required',
            'question.answers.*.order' => 'required',
        ];
    }

    public function updateRankingOrder($value)
    {
        foreach ($value as $key => $item) {
            $this->instance->cmsPropertyBag['answerStruct'][((int)$item['value']) - 1]['order'] = $item['order'];
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

        $this->instance->cmsPropertyBag['answerStruct'] = array_values(
            collect($this->instance->cmsPropertyBag['answerStruct'])
                ->filter(function ($answer) use ($id) {
                    $answerId = is_array($answer) ? $answer['id'] : $answer->id;
                    return $answerId != $id;
                }
                )->toArray()
        );

        if (self::MIN_ANSWER_COUNT < $this->instance->cmsPropertyBag['answerCount']) {
            $this->instance->cmsPropertyBag['answerCount']--;
        }
        $this->createAnswerStruct();
        $this->instance->dirty = true;
    }

    public function addAnswerItem()
    {
        $this->instance->cmsPropertyBag['answerCount']++;
        $this->createAnswerStruct();
    }

    public function rankingUpdated($name, $value)
    {
        $this->createAnswerStruct();
    }

    public function createAnswerStruct()
    {
        $result = [];

        collect($this->instance->cmsPropertyBag['answerStruct'])->each(function ($value, $key) use (&$result) {
            $result[] = (object)['id' => $value['id'], 'order' => $key + 1, 'left' => $value['left'], 'right' => $value['right']];
        })->toArray();

        if (count($this->instance->cmsPropertyBag['answerStruct']) < $this->instance->cmsPropertyBag['answerCount']) {
            for ($i = count($this->instance->cmsPropertyBag['answerStruct']); $i < $this->instance->cmsPropertyBag['answerCount']; $i++) {
                $result[] = (object)[
                    'id'    => Uuid::uuid4(),
                    'order' => $i + 1,
                    'left'  => '',
                    'right' => '',
                ];
            }
        }

        $this->instance->cmsPropertyBag['answerStruct'] = $result;
        $this->instance->cmsPropertyBag['answerCount'] = count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function prepareForSave()
    {
        $this->instance->question['answers'] = array_values(collect($this->instance->cmsPropertyBag['answerStruct'])->map(function ($answer) {
            $answer = $answer instanceof \stdClass ? (array)$answer : $answer;
            return [
                'order' => $answer['order'],
                'left'  => BaseHelper::transformHtmlChars($answer['left']),
                'right' => BaseHelper::transformHtmlChars($answer['right']),
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
            $q = $this->getQuestion();

            $corresponding = (object)[
                'id'     => '',
                'answer' => '',
                'type'   => ''
            ];
            $this->instance->cmsPropertyBag['answerStruct'] = $q->matchingQuestionAnswers->map(function ($answer, $key) use (&$corresponding) {
                if (Str::upper($answer->type) === 'LEFT') {
                    $corresponding = (object)[
                        'id'     => $answer->id,
                        'answer' => $answer->answer,
                    ];
                    return null;
                } else if ($answer->correct_answer_id === $corresponding->id) {
                    return [
                        'id'    => Uuid::uuid4(),
                        'order' => $key + 1,
                        'left'  => BaseHelper::transformHtmlCharsReverse($corresponding->answer,false),
                        'right' => BaseHelper::transformHtmlCharsReverse($answer->answer,false),
                    ];
                } else {
                    throw new \Exception('Mismatch in the answer details, get in contact with the Test-Correct Helpdesk and notify them about this error with question ID ' . $this->instance->questionId);
                }
            })->filter(function ($answer, $key) {
                return $answer != null;
            })->toArray();
            $this->instance->cmsPropertyBag['answerStruct'] = array_values($this->instance->cmsPropertyBag['answerStruct']);
        }
        $this->instance->cmsPropertyBag['answerCount'] = count($this->instance->cmsPropertyBag['answerStruct']);
    }

    public function getTemplate(): string
    {
        return 'matching-question';
    }
}
