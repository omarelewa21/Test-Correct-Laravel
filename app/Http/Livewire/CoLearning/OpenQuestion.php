<?php

namespace tcCore\Http\Livewire\CoLearning;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class OpenQuestion extends Component
{
    use WithCloseable, WithGroups;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];
    public $answer = '';
    public $answered;
    public $question;
    public $questionNumber;
    public $answerNumber;

    public $answerRating;

    public function mount()
    {
        $this->question = $this->answerRating->answer->question;
        $this->answered = $this->answerRating->answer->isAnswered;


        $temp = (array) json_decode($this->answerRating->answer->json);
        if (key_exists('value', $temp)) {
            $this->answer = $temp['value'];
        }
        $this->answer = '
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquam architecto assumenda delectus deleniti incidunt inventore mollitia, nihil pariatur quam suscipit! Autem est mollitia nobis obcaecati praesentium quo quod sapiente ullam!
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquam architecto assumenda delectus deleniti incidunt inventore mollitia, nihil pariatur quam suscipit! Autem est mollitia nobis obcaecati praesentium quo quod sapiente ullam!
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquam architecto assumenda delectus deleniti incidunt inventore mollitia, nihil pariatur quam suscipit! Autem est mollitia nobis obcaecati praesentium quo quod sapiente ullam!
        ';

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        if ($this->question->subtype === 'short') {
            return view('livewire.co-learning.open-question');
        }

        return view('livewire.co-learning.open-medium-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
