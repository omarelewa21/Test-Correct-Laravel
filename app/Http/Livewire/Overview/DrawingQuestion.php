<?php

namespace tcCore\Http\Livewire\Overview;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class DrawingQuestion extends Component
{
    use WithAttachments, WithNotepad, WithCloseable, WithGroups;

    public $question;

    public $number;

    public $drawingModalOpened = false;

    public $answers;

    public $answer;
    public $answered;

    public $additionalText;

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

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.overview.drawing-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
