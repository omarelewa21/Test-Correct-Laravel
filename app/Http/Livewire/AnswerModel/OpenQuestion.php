<?php

namespace tcCore\Http\Livewire\AnswerModel;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Question;

class OpenQuestion extends Component
{
    use WithCloseable;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];
    public $answer = '';
    public $answered;
    public $question;
    public $number;
    public $answers;
    public $editorId;

    public function mount()
    {
        $this->editorId = 'editor_'.$this->question->id;



        $this->answered = [];

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        if ($this->question->subtype === 'short') {
            return view('livewire.answer_model.open-question');
        }

        return view('livewire.answer_model.open-medium-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
