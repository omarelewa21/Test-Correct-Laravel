<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

use Illuminate\Support\Str;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Livewire\Teacher\Cms\Constructor;

class Drawing extends TypeProvider
{
    public function getTranslationKey(): string
    {
        return __('cms.drawing-question');
    }

    public function getTemplate(): string
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

    public function initializePropertyBag($question)
    {
        $svgHelper = new SvgHelper($question['uuid']);

        $this->instance->question['answer_svg'] = $this->getAnswerSvg($svgHelper, $question);
        $this->instance->question['question_svg'] = $svgHelper->getQuestionSvg($question);
        $this->instance->question['grid_svg'] = $question['grid_svg'];
        $this->instance->question['grid'] = $question['grid'];
        $this->instance->question['zoom_group'] = $this->getViewBox($svgHelper, $question);

        $this->instance->question['uuid'] = $question['uuid'];
        $this->instance->question['temp_uuid'] = 'temp-'.$question['uuid'];
        $this->instance->isOldDrawing = $this->isOldDrawingQuestion();

        if (filled($this->instance->question['zoom_group'])) {
            $this->setViewBox($this->instance->question['zoom_group']);
        }
    }

    public function preparePropertyBag()
    {
        $this->instance->question['answer_svg'] = '';
        $this->instance->question['question_svg'] = '';
        $this->instance->question['grid_svg'] = '0.00';
        $this->instance->question['grid'] = '0';
        $this->instance->question['zoom_group'] = '';
        $this->instance->question['question_preview'] = '';
        $this->instance->question['question_correction_model'] = '';
        $this->instance->question['uuid'] = (string)Str::uuid();
        $this->instance->question['temp_uuid'] = 'temp-'.$this->instance->question['uuid'];
        $this->instance->backgroundImage = null;
        $this->instance->isOldDrawing = $this->isOldDrawingQuestion();
    }

    public function handleUpdateDrawingData($data)
    {
        $this->instance->question['answer_svg'] = $data['svg_answer'];
        $this->instance->question['question_svg'] = $data['svg_question'];
        $this->instance->question['grid_svg'] = $data['grid_size'];
        $this->instance->question['zoom_group'] = $data['svg_zoom_group'];
        $this->instance->question['svg_date_updated'] = now();
        $this->instance->isOldDrawing = $this->isOldDrawingQuestion();

        $this->setViewBox($data['svg_zoom_group']);

        $this->updateFilesystemData($data);

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

    private function setViewBox($data)
    {
        $this->instance->cmsPropertyBag['viewBox'] = sprintf('%s %s %s %s',
            $data['x'],
            $data['y'],
            $data['width'],
            $data['height']
        );
    }

    public function performAfterSaveActions($response)
    {
        $this->unprepareForSave();
        $svgHelper = new SvgHelper($this->instance->question['temp_uuid']);

        if ($this->instance->question['uuid'] === $response->original->question->uuid) {
            $svgHelper->rename($this->instance->question['uuid']);
        } else {
            $svgHelper->rename($response->original->question->uuid);
        }

        (new SvgHelper($this->instance->question['temp_uuid']))->delete();
    }

    /**
     * @param $data
     * @return void
     */
    private function updateFilesystemData($data): void
    {
        $svgHelper = new SvgHelper($this->instance->question['temp_uuid']);

        if (array_key_exists('images', $this->instance->cmsPropertyBag)) {
            if (array_key_exists('answer', $this->instance->cmsPropertyBag['images'])) {
                collect($this->instance->cmsPropertyBag['images']['answer'])->each(function ($content, $identifier) use ($svgHelper) {
                    $svgHelper->addAnswerImage($identifier, $content);
                });
            }
            if (array_key_exists('question', $this->instance->cmsPropertyBag['images'])) {
                collect($this->instance->cmsPropertyBag['images']['question'])->each(function ($content, $identifier) use ($svgHelper) {
                    $svgHelper->addQuestionImage($identifier, $content);
                });
            }
        }

        $svgHelper->setViewBox($data['svg_zoom_group']);
        $svgHelper->updateAnswerLayer($data['cleaned_answer_svg']);
        $svgHelper->updateQuestionLayer($data['cleaned_question_svg']);

        $svgHelper->updateQuestionPNG($data['png_question_preview_string']);
        $svgHelper->updateCorrectionModelPNG($data['png_correction_model_string']);

    }

    private function getAnswerSvg(SvgHelper $svgHelper, $q)
    {
        if ($svgHelper->getAnswerLayerFromSVG()) {
            return $svgHelper->getAnswerLayerFromSVG(true);
        }
        return $q['answer_svg'];
    }

    private function getViewBox(SvgHelper $svgHelper, $q)
    {
        if($svgHelper->getViewBox() !== '0 0 0 0') {
            return $svgHelper->makeViewBoxArray($svgHelper->getViewBox());
        }
        return  json_decode($q['zoom_group'], true);
    }

    public function drawingToolName()
    {
        if ($this->instance instanceof Constructor) {
            if ($this->instance->action == 'edit') {
                return $this->instance->groupQuestionQuestionId === '' ? $this->instance->testQuestionId : $this->instance->groupQuestionQuestionId;
            }
            return $this->instance->questionEditorId;
        }

        return $this->instance->question['uuid'];
    }

    public function clearQuestionBag(){
        $this->instance->question['answer_svg'] = '';
        $this->instance->question['question_svg'] = '';
        $this->instance->question['grid_svg'] = '0.00';
        $this->instance->question['zoom_group'] = '';
        $this->instance->question['question_preview'] = '';
        $this->instance->question['question_correction_model'] = '';
    }
}
