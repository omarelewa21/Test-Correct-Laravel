<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;
use tcCore\Question;

class OpenQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithQuestionTimer, WithGroups;

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

        $this->attachments = $this->question->attachments;
    }


    public function updatedAnswer($value)
    {
        $json = json_encode((object) ['value' => $this->answer]);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

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
