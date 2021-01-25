<?php

namespace tcCore\Http\Livewire\Question;

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

    public function mount()
    {
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
            return view('livewire.question.open-question', compact('question'));
        }

        return view('livewire.question.open-medium-question', compact('question'));
    }
}
