<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use tcCore\Answer;
use tcCore\DrawingQuestion as DrawingQuestionModel;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;

abstract class DrawingQuestion extends TCComponent
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups;

    public $question;

    public $number;

    public $drawingModalOpened = false;

    public $answers;

    public $answer;

    public $additionalText;

    public $playerInstance;

    public $backgroundImage = null;

    public $answer_svg = null;
    public $question_svg = null;
    public $grid_svg = '0.00';
    public $grid = '0';

    public $usesNewDrawingTool = false;

    public $testTakeUuid;

    protected function getListeners()
    {
        return [
            'drawing_data_updated' => 'handleUpdateDrawingData',
        ];
    }

    public function mount()
    {
        $this->initPlayerInstance();

        $svgHelper = new SvgHelper($this->question->uuid);

        $this->question_svg = $svgHelper->getQuestionSvg($this->question);

        $this->grid_svg = $this->question->grid_svg;
        $this->grid = $this->question->grid;
        $this->backgroundImage = $this->question->getBackgroundImage();

        $answer = Answer::where('id', $this->answers[$this->question->uuid]['id'])
            ->where('question_id', $this->question->id)
            ->first();
        if ($answer->json) {
            $answerJson = json_decode($answer->json);
            $this->answer = $answerJson->answer;
            $this->additionalText = $answerJson->additional_text;
            if (property_exists($answerJson, 'answer_svg') && $answerJson->answer_svg) {
                $this->answer_svg = $answerJson->answer_svg;
            }
            if (property_exists($answerJson, 'grid_size') && $answerJson->grid_size) {
                $this->grid_svg = $answerJson->grid_size;
            }
        }

        $this->usesNewDrawingTool = Auth::user()->schoolLocation()->value('allow_new_drawing_question');
    }

    private function getQuestionSvg(SvgHelper $svgHelper, $q)
    {
        if ($svgHelper->getQuestionLayerFromSVG()) {
            return $svgHelper->getQuestionLayerFromSVG(true);
        }
        return $q['question_svg'];
    }

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function updatedAnswer($value)
    {

        $this->answer = $this->saveImageAndReturnUrl($value);

        $json = json_encode([
            'answer' => $this->answer,
            'additional_text' => $this->additionalText,
            'answer_svg' => $this->answer_svg,
            'grid_size' => $this->grid_svg,
        ]);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

        $this->drawingModalOpened = false;
        $this->emitTo('question.navigation', 'current-question-answered', $this->number);
    }

    public function render()
    {
        return view('livewire.question.drawing-question');
    }

    private function saveImageAndReturnUrl($image)
    {
        $answer = Answer::where('id', $this->answers[$this->question->uuid]['id'])
            ->where('question_id', $this->question->id)
            ->first();

        Storage::put($answer->getDrawingStoragePath(), $image);

        return $answer->uuid;
    }

    private function initPlayerInstance()
    {
        $this->playerInstance = 'eppi_' . rand(1000, 9999999);
    }

    public function handleUpdateDrawingData($data)
    {
        $svg = sprintf('<svg viewBox="%s %s %s %s" class="w-full h-full" id="" xmlns="http://www.w3.org/2000/svg">
                    <defs><style>%s</style></defs>
                    <g id="grid-preview-svg" stroke="#c3d0ed" stroke-width="1">%s</g>
                    <g class="question-svg">%s</g>
                    <g class="answer-svg">%s</g>
                </svg>',
            $data['svg_zoom_group']['x'],
            $data['svg_zoom_group']['y'],
            $data['svg_zoom_group']['width'],
            $data['svg_zoom_group']['height'],
            DrawingQuestionModel::getEmbeddedFontForSVG(),
            base64_decode($data['svg_grid']),
            base64_decode($data['svg_question']),
            base64_decode($data['svg_answer'])
        );
        Storage::putFileAs(
            'drawing_question_answers',
            $data['png_correction_model_string'],
            sprintf('%s.png', $this->answers[$this->question->uuid]['uuid'])
        );

        $this->grid_svg = $data['grid_size'];
        $this->answer_svg = $data['svg_answer'];

        $this->updatedAnswer($svg);
    }
}
