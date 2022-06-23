<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use Composer\Package\Package;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Question;
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

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }

    }

    public function render()
    {
        return view('livewire.test_take_overview_preview.matrix-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        $selectedAnswers = count(array_filter($this->answerStruct));
        return $this->subQuestions->count() === $selectedAnswers;
    }
}
