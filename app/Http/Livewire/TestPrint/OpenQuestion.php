<?php

namespace tcCore\Http\Livewire\TestPrint;

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
    public $number;
    public $answers;
    public $editorId;
    public $attachment_counters;

    public function mount()
    {
        $this->editorId = 'editor_'.$this->question->id;
        $this->answered = [];
        $this->answer = $this->question->answer;
        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        $this->question->caption = 'hello world';
        if ($this->question->subtype === 'short') {
            return view('livewire.test_print.open-question');
        }
        return view('livewire.test_print.open-medium-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
