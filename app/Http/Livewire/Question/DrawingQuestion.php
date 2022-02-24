<?php

namespace tcCore\Http\Livewire\Question;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithUpdatingHandling;

class DrawingQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups, WithUpdatingHandling;

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

    public $usesNewDrawingTool = true;

    protected function getListeners()
    {
        return [
            'drawing_data_updated' => 'handleUpdateDrawingData',
        ];
    }

    public function mount()
    {
        $this->initPlayerInstance();

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
        }

        $this->question_svg = $this->question->question_svg;
        $this->grid_svg = $this->question->grid_svg;
        $this->backgroundImage = $this->question->getBackgroundImage();
    }

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function updatedAnswer($value)
    {

        $this->answer = $this->saveImageAndReturnUrl($value);

        $json = json_encode([
            'answer'          => $this->answer,
            'additional_text' => $this->additionalText,
            'answer_svg'      => $this->answer_svg,
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
        $this->playerInstance = 'eppi_'.rand(1000, 9999999);
    }

    public function handleUpdateDrawingData($data)
    {
        $svg = sprintf('<svg viewBox="%s %s %s %s" class="w-full h-full" id="" xmlns="http://www.w3.org/2000/svg">
                    <g class="question-svg">%s</g>
                    <g class="answer-svg">%s</g>
                    <g id="grid-preview-svg" stroke="var(--all-BlueGrey)" stroke-width="1"></g>
                </svg>',
                    $data['svg_zoom_group']['x'],
                    $data['svg_zoom_group']['y'],
                    $data['svg_zoom_group']['width'],
                    $data['svg_zoom_group']['height'],
                    base64_decode($data['svg_question']),
                    base64_decode($data['svg_answer'])
                );

         $base64 = base64_encode($svg);



        $this->answer_svg = $data['svg_answer'];



        $this->updatedAnswer($svg);
    }
}
