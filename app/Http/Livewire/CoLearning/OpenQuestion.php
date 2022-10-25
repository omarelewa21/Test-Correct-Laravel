<?php

namespace tcCore\Http\Livewire\CoLearning;

use Livewire\Component;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class OpenQuestion extends Component
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
        if(isset($data)) {
            $this->answerRatingId = $data[0];
            $this->questionNumber = $data[1];
            $this->answerNumber = $data[2];
        }

        $this->answerRating = AnswerRating::find($this->answerRatingId);

        $this->question = $this->answerRating->answer->question;
        $this->answered = $this->answerRating->answer->isAnswered;


        $temp = (array) json_decode($this->answerRating->answer->json);
        if (key_exists('value', $temp)) {
            $this->answer = $temp['value'];
        }


        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.co-learning.open-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
