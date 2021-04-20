<?php

namespace tcCore\Http\Livewire\Question;

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

    public $subQuestions;
    public $questionAnswers;
    public $chosenAnswer;

    public $answerStruct;

    public function mount()
    {
        $this->subQuestions = $this->question->matrixQuestionSubQuestions;
        $this->questionAnswers = $this->question->matrixQuestionAnswers;

        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
        }

    }

    public function render()
    {
        return view('livewire.question.matrix-question');
    }

    public function updating($name, $value)
    {
        if ($name == 'answer') {
            $answerIds = Str::of($value)->explode(':');
            $this->answerStruct[$answerIds[0]] = $answerIds[1];

            $json = json_encode($this->answerStruct);

            Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);
        }

    }
}
