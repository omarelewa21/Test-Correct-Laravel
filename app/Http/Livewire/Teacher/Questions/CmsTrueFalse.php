<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsTrueFalse
{
    private $instance;

    public function __construct(OpenShort $instance) {
        $this->instance = $instance;
    }

    public function mergeRules(&$rules)
    {
        $rules += [
            'question.answers' => 'required|array|min:2|max:2',
        ];
    }

    public function updatedTfTrue($val)
    {
        $this->instance->tfTrue = ($val == 'true');
    }

    public function tfIsActiveAnswer($args)
    {
        $val = $args[0];
        return ($this->instance->tfTrue == ($val == 'true'));
    }

    public function initializePropertyBag($q){

        $q->multipleChoiceQuestionAnswers->each(function ($answer) {
            if (Str::lower($answer->answer) === 'juist' && $answer->score > 0) {
                $this->instance->tfTrue = true;
            }
            if (Str::lower($answer->answer) === 'onjuist' && $answer->score > 0) {
                $this->instance->tfTrue = false;
            }
        });
    }

    public function prepareForSave()
    {
        $result = [];
        $nr = 1;
        foreach(['Juist' => true,'Onjuist' => false] as $option => $value){
            $result[] = [
                'order' => $nr,
                'answer' => $option,
                'score' => ($this->instance->tfTrue === $value) ? $this->instance->question['score'] : 0,
            ];
            $nr++;
        }
        $this->instance->question['answers'] = $result;
        unset($this->instance->question['answer']);
    }

    public function getTranslationKey() {
        return 'cms.multiplechoice-question-truefalse';
    }



}
