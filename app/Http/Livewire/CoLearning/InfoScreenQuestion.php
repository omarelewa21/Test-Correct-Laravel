<?php

namespace tcCore\Http\Livewire\CoLearning;

use Livewire\Component;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class InfoScreenQuestion extends Component
{
    use WithCloseable, WithGroups;

    protected $listeners = [
        'questionUpdated' => 'questionUpdated',
        'getNextAnswerRating' => 'initializeComponent',
    ];
    public $answer = '';
    public $answered;
    public $question;

    public $questionNumber;
    public $answerNumber;

    public $answerRatingId;
    private $answerRating;

    public function mount()
    {
        $this->initializeComponent();
    }

    public function initializeComponent($data = null)
    {
        $this->answered = true;


        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.co-learning.info-screen-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
