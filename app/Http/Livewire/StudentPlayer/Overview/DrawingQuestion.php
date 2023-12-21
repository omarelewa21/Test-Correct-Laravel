<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Answer;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithStudentPlayerOverview;
use tcCore\Http\Livewire\StudentPlayer\DrawingQuestion as AbstractDrawingQuestion;
use tcCore\Http\Traits\WithAttachments;

class DrawingQuestion extends AbstractDrawingQuestion
{
    use WithGroups;
    use WithStudentPlayerOverview;
    use WithAttachments;

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
