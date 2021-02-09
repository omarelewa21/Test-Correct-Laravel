<?php

namespace tcCore\Http\Livewire\Question;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class DrawingQuestion extends Component
{
    use WithAttachments, WithNotepad;

    public $question;

    public $number;

    public $drawingModalOpened = false;

    public $answers;

    public $answer;

    public $additionalText;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function updated($name, $value) {

        if ($name == 'answer') {
            $this->answer = $this->saveImageAndReturnUrl($value);

            $json = json_encode([
                'answer' => $this->answer,
                'additional_text' => $this->additionalText,
            ]);

            Answer::where([
                ['id', $this->answers[$this->question->uuid]['id']],
                ['question_id', $this->question->id],
            ])->update(['json' => $json]);

            $this->drawingModalOpened = false;

        }
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

        return route('student.drawing-question-answer', $answer->uuid);
    }
}
