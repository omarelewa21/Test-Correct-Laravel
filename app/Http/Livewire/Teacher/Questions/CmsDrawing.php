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

    public function initializePropertyBag($q)
    {
        $this->instance->question['answer_svg'] = $q['answer_svg'];
        $this->instance->question['question_svg'] = $q['question_svg'];
        $this->instance->question['grid_svg'] = $q['grid_svg'];
        $this->instance->question['zoom_group'] = json_decode($q['zoom_group'], true);

        if (filled($this->instance->question['zoom_group'])) {
            $this->setViewbox($this->instance->question['zoom_group']);
        }
    }

    public function preparePropertyBag()
    {
        $this->instance->question['answer_svg'] = '';
        $this->instance->question['question_svg'] = '';
        $this->instance->question['grid_svg'] = '0.00';
        $this->instance->question['zoom_group'] = '';
    }

    public function handleUpdateDrawingData($data)
    {
        $this->instance->question['answer_svg'] = $data['svg_answer'];
        $this->instance->question['question_svg'] = $data['svg_question'];
        $this->instance->question['grid_svg'] = $data['svg_grid'];
        $this->instance->question['zoom_group'] = $data['svg_zoom_group'];

        $this->setViewbox($data['svg_zoom_group']);
    }

    public function prepareForSave()
    {
        $this->instance->question['zoom_group'] = json_encode($this->instance->question['zoom_group']);
    }

    public function UnprepareForSave()
    {
        $this->instance->question['zoom_group'] = json_decode($this->instance->question['zoom_group']);
    }

    private function setViewbox($data)
    {
        $this->instance->cmsPropertyBag['viewBox'] = sprintf('%s %s %s %s',
            $data['x'],
            $data['y'],
            $data['width'],
            $data['height']
        );
    }
}
