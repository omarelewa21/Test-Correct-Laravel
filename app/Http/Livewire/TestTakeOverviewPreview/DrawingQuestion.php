<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class DrawingQuestion extends Component
{
    use WithAttachments, WithNotepad, WithCloseable;

    public $question;

    public $number;

    public $drawingModalOpened = false;

    public $answers;

    public $answer;
    public $answered;

    public $additionalText;
    public $imgSrc;

    public function mount()
    {
        $answer = Answer::where('id', $this->answers[$this->question->uuid]['id'])
            ->where('question_id', $this->question->id)
            ->first();
        if ($answer->json) {
            $this->answer = json_decode($answer->json)->answer;
            $this->additionalText = json_decode($answer->json)->additional_text;
        }

        $this->answered = $this->answers[$this->question->uuid]['answered'];

        $file = Storage::get($answer->getDrawingStoragePath());

        if (substr($file, 0, 4) ==='<svg') {
            $this->imgSrc = "data:image/svg+xml;charset=UTF-8," . rawurlencode($file);
        }else{
            $this->imgSrc = "data:image/png;base64," . base64_encode(file_get_contents($file));
        }



        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.test_take_overview_preview.drawing-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }

    private function getStudenAnswerImage(Answer $answer)
    {
        $file = Storage::get($answer->getDrawingStoragePath());

        if (substr($file, 0, 4) ==='<svg') {
            header('Content-type: image/svg+xml');
            echo $file;
            die;
        }

        return file_get_contents($file);
    }
}
