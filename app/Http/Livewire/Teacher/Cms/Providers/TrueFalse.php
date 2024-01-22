<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use Illuminate\Support\Str;
use tcCore\Http\Interfaces\QuestionCms;
use tcCore\Http\Traits\WithCmsCustomRulesHandling;
use tcCore\Rules\TrueFalseRule;

class TrueFalse extends TypeProvider
{
    use WithCmsCustomRulesHandling;

    public function __construct(QuestionCms $instance)
    {
        parent::__construct($instance);

        if (!array_key_exists('tfTrue', $this->instance->cmsPropertyBag)) {
            $this->instance->cmsPropertyBag['tfTrue'] = [];
        }

    }

    public function mergeRules(&$rules)
    {
        $rules += [
            'question.answers' => [
                'required',
                'array',
                ['min' => 2],
                ['max' => 2],
                new TrueFalseRule
            ]
        ];
    }

    public function updatedTfTrue($val)
    {
        $this->instance->cmsPropertyBag['tfTrue'] = ($val == 'true');
    }

    public function tfIsActiveAnswer($args)
    {
        $val = $args[0];
        return ($this->instance->cmsPropertyBag['tfTrue'] == ($val == 'true'));
    }

    public function initializePropertyBag($question)
    {
        $question->multipleChoiceQuestionAnswers->each(function ($answer) {
            if (Str::lower($answer->answer) === 'juist' && $answer->score > 0) {
                $this->instance->cmsPropertyBag['tfTrue'] = 'true';
            }
            if (Str::lower($answer->answer) === 'onjuist' && $answer->score > 0) {
                $this->instance->cmsPropertyBag['tfTrue'] = 'false';
            }
        });
    }

    public function prepareForSave()
    {
        $result = [];
        $nr = 1;
        foreach (['Juist' => 'true', 'Onjuist' => 'false'] as $option => $value) {
            $result[] = [
                'order'  => $nr,
                'answer' => $option,
                'score'  => ($this->instance->cmsPropertyBag['tfTrue'] === $value) ? $this->instance->question['score'] : 0,
            ];
            $nr++;
        }
        $this->instance->question['answers'] = $result;
        unset($this->instance->question['answer']);
    }

    public function getTranslationKey(): string
    {
        return __('cms.multiplechoice-question-truefalse');
    }

    public function getTemplate(): string
    {
        return 'true-false-question';
    }

    public function createAnswerStruct()
    {
    }
}
