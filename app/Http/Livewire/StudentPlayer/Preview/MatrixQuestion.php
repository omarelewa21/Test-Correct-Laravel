<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use Illuminate\Support\Str;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;

class MatrixQuestion extends TCComponent
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $question;
    public $testId;
    public $number;
    public $answers;

    public $answer;
    public $subQuestions;
    public $questionAnswers;
    public $answerStruct;

    public function mount()
    {
        $this->subQuestions = $this->question->matrixQuestionSubQuestions;
        $this->questionAnswers = $this->question->matrixQuestionAnswers;
    }

    public function render()
    {
        return view('livewire.student-player.preview.matrix-question');
    }

    public function updatingAnswer($value)
    {
        $answerIds = Str::of($value)->explode(':');
        $this->answerStruct[$answerIds[0]] = $answerIds[1];
    }
}
