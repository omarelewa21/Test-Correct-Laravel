<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;
use tcCore\Http\Livewire\StudentPlayer\DrawingQuestion as AbstractDrawingQuestion;

class DrawingQuestion extends AbstractDrawingQuestion
{
    use WithAttachments;
    use WithGroups;
    use WithNotepad;


    public $answered;

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
        return view('livewire.student-player.overview.drawing-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
