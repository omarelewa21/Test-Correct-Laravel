<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class CmsMultipleChoice
{
    private $instance;

    public function __construct(OpenShort $instance) {
        $this->instance = $instance;
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


    public function initializePropertyBag($q){
        $this->instance->mcAnswerStruct = $q->multipleChoiceQuestionAnswers->map(function ($answer, $key) {
            return [
                'id'     => Uuid::uuid4(),
                'order'  => $key + 1,
                'score'  => $answer->score,
                'answer' => $answer->answer,
            ];
        })->toArray();
    }

    public function prepareForSave()
    {
        $this->instance->question['answers'] = array_values(collect($this->instance->mcAnswerStruct)->map(function($answer){
            return [
                'order' => $answer['order'],
                'answer' => $answer['answer'],
                'score' => $answer['score'],
            ];
        })->toArray());
        unset($this->instance->question['answer']);
        $this->instance->question['score'] = collect($this->instance->mcAnswerStruct)->sum('score');

    }

    public function createMCAnswerStruct()
    {
        $result = [];

        collect($this->instance->mcAnswerStruct)->each(function ($value, $key) use (&$result) {
            $result[] = (object)['id' => $value['id'], 'order' => $key + 1, 'answer' => $value['answer'], 'score' => (int) $value['score']];
        })->toArray();

        if(count($this->instance->mcAnswerStruct) < $this->instance->mcAnswerCount){
            for($i = count($this->instance->mcAnswerStruct);$i < $this->instance->mcAnswerCount;$i++){
                $result[] = (object)[
                    'id'    => Uuid::uuid4(),
                    'order' => $i+1,
                    'score' => 0,
                    'answer' => ''
                ];
            }
        }

        $this->instance->mcAnswerStruct  = $result;
        $this->instance->mcAnswerCount = count($this->instance->mcAnswerStruct);
    }


    public function getTranslationKey() {
        return 'cms.multiplechoice-question-multiplechoice';
    }

    // Multiple Choice
    public function updateMCOrder($args)
    {
        $value = $args[0];

        foreach($value as $key => $item){
            $this->instance->mcAnswerStruct[((int) $item['value'])-1]['order'] = $item['order'];
        }

        $this->instance->mcAnswerStruct = array_values(collect($this->instance->mcAnswerStruct)->sortBy('order')->toArray());
        $this->createMCAnswerStruct();

    }

    public function mcCanDelete()
    {
        return $this->instance->mcAnswerMinCount < count($this->instance->mcAnswerStruct);
    }

    public function mcDelete($id)
    {

        if(!$this->mcCanDelete()) {
            return;
        }

        $this->instance->mcAnswerStruct = array_values(collect($this->instance->mcAnswerStruct)->filter(function($answer) use ($id){
            return $answer['id'] != $id;
        })->toArray());

        if($this->instance->mcAnswerMinCount < $this->instance->mcAnswerCount) {
            $this->instance->mcAnswerCount--;
        }
        $this->createMCAnswerStruct();
    }

    public function mcAddAnswerItem()
    {
        $this->instance->mcAnswerCount++;
        $this->createMCAnswerStruct();
    }

    public function mcUpdated($args)
    {
        $this->createMCAnswerStruct();
    }



}
