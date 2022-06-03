<?php

namespace tcCore\Http\Livewire\AnswerModel;

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
    use WithNotepad, withCloseable, WithGroups;

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

        $this->question->matrixQuestionAnswerSubQuestions->each(function($matrixQuestionAnswerSubQuestion){
            $this->answerStruct[$matrixQuestionAnswerSubQuestion->matrix_question_sub_question_id] =$matrixQuestionAnswerSubQuestion->matrix_question_answer_id;
        });


        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }

    }

    public function render()
    {
        return view('livewire.answer_model.matrix-question');
    }

}
