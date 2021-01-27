<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Question;

class OpenQuestion extends Component
{
    protected $listeners = ['questionUpdated' => 'questionUpdated'];
    public $answer = '';
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
    }


    public function updatedAnswer($value)
    {
        $json = json_encode((object) ['value' => $this->answer]);

        Answer::where([
            ['id', $this->answers[$this->question->uuid]['id']],
            ['question_id', $this->question->id],
        ])->update(['json' => $json]);

//        $this->emitUp('updateAnswer', $this->uuid, $value);
    }

    public function render()
    {
        if ($this->question->subtype === 'short') {
            return view('livewire.overview.open-question', compact('question'));
        }

        return view('livewire.overview.open-medium-question', compact('question'));
    }
}
