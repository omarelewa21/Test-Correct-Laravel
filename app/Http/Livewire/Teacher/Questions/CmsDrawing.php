<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsDrawing
{
    private $instance;
    public $requiresAnswer = true;

    public $emptyCanvas = true;

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
        return filled($this->instance->question['answer']) && blank($this->instance->question['zoom_group']);
    }

    public function mergeRules(&$rules)
    {
        $rules += [
            'question.answer_svg'   => 'sometimes|required',
            'question.question_svg' => 'sometimes',
            'question.grid_svg'     => 'sometimes',
        ];
    }

    public function initializePropertyBag($q)
    {
        $this->instance->question['answer_svg'] = $q['answer_svg'];
        $this->instance->question['question_svg'] = $q['question_svg'];
        $this->instance->question['grid_svg'] = $q['grid_svg'];
        $this->instance->question['zoom_group'] = json_decode($q['zoom_group'], true);
        $this->instance->question['question_preview'] = $q['question_preview'];
        $this->instance->question['question_correction_model'] = $q['question_correction_model'];

        if (filled($this->instance->question['zoom_group'])) {
            $this->setViewbox($this->instance->question['zoom_group']);
            $this->emptyCanvas = false;
        }
    }

    public function preparePropertyBag()
    {
        $this->instance->question['answer_svg'] = '';
        $this->instance->question['question_svg'] = '';
        $this->instance->question['grid_svg'] = '0.00';
        $this->instance->question['zoom_group'] = '';
        $this->instance->question['question_preview'] = '';
        $this->instance->question['question_correction_model'] = '';
        $this->emptyCanvas = true;
    }

    public function handleUpdateDrawingData($data)
    {
        $this->instance->question['answer_svg'] = $data['svg_answer'];
        $this->instance->question['question_svg'] = $data['svg_question'];
        $this->instance->question['grid_svg'] = $data['grid_size'];
        $this->instance->question['zoom_group'] = $data['svg_zoom_group'];
        $this->instance->question['question_preview'] = $data['png_question_preview_string'];
        $this->instance->question['question_correction_model'] = $data['png_correction_model_string'];

        $this->setViewbox($data['svg_zoom_group']);
        $this->emptyCanvas = false;

        $this->instance->dirty = true;
    }

    public function prepareForSave()
    {
        $this->instance->question['zoom_group'] = json_encode($this->instance->question['zoom_group']);
    }

    public function unprepareForSave()
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

    public function isEmptyCanvas()
    {
        return $this->emptyCanvas;
    }

    public function drawingToolName()
    {
        if ($this->instance->action == 'edit') {
            return $this->instance->groupQuestionQuestionId === '' ? $this->instance->testQuestionId : $this->instance->groupQuestionQuestionId;
        }
        return $this->instance->questionEditorId;
    }
}
