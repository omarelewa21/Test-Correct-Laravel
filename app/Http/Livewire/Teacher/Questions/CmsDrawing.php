<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Question;

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
        $svgHelper = new SvgHelper($q['uuid']);

        $this->instance->question['answer_svg'] = $this->getAnswerSvg($svgHelper, $q);
        $this->instance->question['question_svg'] = $svgHelper->getQuestionSvg($q);
        $this->instance->question['grid_svg'] = $q['grid_svg'];
        $this->instance->question['zoom_group'] = $this->getViewBox($svgHelper, $q);

        $this->instance->question['uuid'] = $q['uuid'];

        if (filled($this->instance->question['zoom_group'])) {
            $this->setViewBox($this->instance->question['zoom_group']);
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
        $this->instance->question['uuid'] = (string)Str::uuid();
    }

    public function handleUpdateDrawingData($data)
    {
        $this->instance->question['answer_svg'] = $data['svg_answer'];
        $this->instance->question['question_svg'] = $data['svg_question'];
        $this->instance->question['grid_svg'] = $data['grid_size'];
        $this->instance->question['zoom_group'] = $data['svg_zoom_group'];

        $this->setViewBox($data['svg_zoom_group']);

        $this->updateFilesystemData($data);
    }

    public function prepareForSave()
    {
        logger([
            'value' => $this->instance->question['zoom_group'],
            'json' => json_encode($this->instance->question['zoom_group']),
            ]
        );
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
        $svgHelper = new SvgHelper($this->instance->question['uuid']);

        if ($this->instance->question['uuid'] === $response->original->question->uuid) {
            return;
        }
        $svgHelper->rename($response->original->question->uuid);
    }

    /**
     * @param $data
     * @return void
     */
    private function updateFilesystemData($data): void
    {
        $svgHelper = new SvgHelper($this->instance->question['uuid']);

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

        $svgHelper->updateAnswerLayer($data['cleaned_answer_svg']);
        $svgHelper->updateQuestionLayer($data['cleaned_question_svg']);

        $svgHelper->updateQuestionPNG($data['png_question_preview_string']);
        $svgHelper->updateCorrectionModelPNG($data['png_correction_model_string']);

        $svgHelper->setViewBox($data['svg_zoom_group']);
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
}
