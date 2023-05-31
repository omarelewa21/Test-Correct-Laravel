<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class OpenQuestion extends TCComponent
{
    use WithCloseable;
    use WithGroups;

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

        $temp = (array) json_decode($this->answers[$this->question->uuid]['answer']);
        if (key_exists('value', $temp)) {
            $this->answer = BaseHelper::transformHtmlCharsReverse($temp['value'], false);
        }

        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.student-player.overview.open-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
