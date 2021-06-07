<?php

namespace tcCore\Http\Livewire\Overview;

use Composer\Package\Package;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;

class MatrixQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups;

    public $question;
    public $number;
    public $answers;

    public $answer;
    public $answered;
    public $subQuestions;
    public $questionAnswers;
    public $answerStruct;

    public function mount()
    {
        $this->subQuestions = $this->question->matrixQuestionSubQuestions;
        $this->questionAnswers = $this->question->matrixQuestionAnswers;

        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
            $this->answered = true;
        }

    }

    public function render()
    {
        return view('livewire.overview.matrix-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
