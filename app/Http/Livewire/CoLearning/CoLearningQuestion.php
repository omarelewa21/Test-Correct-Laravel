<?php

namespace tcCore\Http\Livewire\CoLearning;

use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

abstract class CoLearningQuestion extends Component
{
    public $answer = null;
    public $answered;
    public $question;
    public $questionNumber;
    public $answerNumber;

    public $answerRatingId;
    protected $answerRating;

    public $originalUrl;

    protected $listeners = [
        'getNextAnswerRating' => 'initializeComponent',
    ];

    public function mount()
    {
        $this->originalUrl = \Livewire::originalUrl();

        $this->initializeComponent();
    }

    public function initializeComponent($data = null)
    {
        if(isset($data)) {
            $this->answerRatingId = $data[0];
            $this->questionNumber = $data[1];
            $this->answerNumber = $data[2];
        }

        $this->answerRating = AnswerRating::find($this->answerRatingId);

        $this->question = $this->answerRating->answer->question;

        $this->redirectByWrongQuestionType();

        $this->answered = $this->answerRating->answer->isAnswered;

        $this->handleGetAnswerData();

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function redirectByWrongQuestionType()
    {
        if(!Str::endsWith(get_class($this), $this->question->type)) {
             return redirect($this->originalUrl);
        }
    }

    abstract public function render();

    abstract public function isQuestionFullyAnswered() : bool;

    abstract protected function handleGetAnswerData();

}