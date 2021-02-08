<?php

namespace tcCore\Http\Livewire\Question;

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
        }

        $json = json_encode([
            'answer' => $this->answer,
            'additional_text' => $this->additionalText,
        ]);

        Answer::where([
            ['id', $this->answers[$this->question->uuid]['id']],
            ['question_id', $this->question->id],
        ])->update(['json' => $json]);
    }

    public function render()
    {
        return view('livewire.question.drawing-question');
    }

    private function saveImageAndReturnUrl($answer)
    {
        return 'http://testportal.test-correct.test/custom/imageload.php?filename=1612793932.png&amp;type=drawing';
    }
}
