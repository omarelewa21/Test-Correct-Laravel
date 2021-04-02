<?php

namespace tcCore\Http\Livewire\Overview;

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

        $temp = (array) json_decode($this->answers[$this->question->uuid]['answer']);
        if (key_exists('value', $temp)) {
            $this->answer = $temp['value'];
        }

        $this->answered = $this->answers[$this->question->uuid]['answered'];
    }

    public function render()
    {
        if ($this->question->subtype === 'short') {
            return view('livewire.overview.open-question', compact('question'));
        }

        return view('livewire.overview.open-medium-question', compact('question'));
    }
}
