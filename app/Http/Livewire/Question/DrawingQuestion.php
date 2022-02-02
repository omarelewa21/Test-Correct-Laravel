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
            $this->answer = json_decode($answer->json)->answer;
            $this->additionalText = json_decode($answer->json)->additional_text;
        }

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
        $svg = base64_encode(
            sprintf('<svg class="w-full h-full" id="" xmlns="http://www.w3.org/2000/svg" style="--cursor-type-locked:var(--cursor-crosshair); --cursor-type-draggable:var(--cursor-crosshair);">
                    <g class="answer-svg">%s</g>
                    <g class="question-svg">%s</g>
                    <g id="grid-preview-svg" stroke="var(--all-BlueGrey)" stroke-width="1"></g>
                </svg>',
                base64_decode($data['svg_answer']),
                base64_decode($data['svg_question'])
            )
        );
        dd('<img src="'.$svg.'"/>');

        $this->updatedAnswer($svg);
    }
}
