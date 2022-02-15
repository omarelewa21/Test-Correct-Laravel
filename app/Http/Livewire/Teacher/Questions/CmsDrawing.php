<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsDrawing
{
    private $instance;
    public $requiresAnswer = true;

    public function __construct(OpenShort $instance)
    {
        $this->instance = $instance;
    }

    public function getTranslationKey()
    {
        return __('cms.drawing-question');
    }

    public function getTemplate()
    {
        return 'drawing-question';
    }

    public function isOldDrawingQuestion()
    {
        return false;
    }

    public function mergeRules(&$rules)
    {
        $rules += [
            'question.answer_svg'   => 'sometimes|required',
            'question.question_svg' => 'sometimes|required',
            'question.grid_svg'     => 'sometimes|required',
        ];
    }

    public function initializePropertyBag($q) {
        $this->instance->question['answer_svg'] =$q['answer_svg'];
        $this->instance->question['question_svg'] =$q['question_svg'];
        $this->instance->question['grid_svg'] =$q['grid_svg'];
    }

    public function preparePropertyBag() {
        $this->instance->question['answer_svg'] = '';
        $this->instance->question['question_svg'] = '';
        $this->instance->question['grid_svg'] = '0.00';
    }
}
