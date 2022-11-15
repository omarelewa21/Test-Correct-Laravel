<?php

namespace tcCore\Http\Livewire\Questions\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithUpdatingHandling;

class OpenQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups, WithUpdatingHandling;

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

//        $this->attachments = $this->question->attachments;
    }


    public function updatedAnswer($value)
    {
        $json = json_encode((object) ['value' => $this->answer]);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

        $this->emitTo('question.navigation','current-question-answered', $this->number);
    }

    public function render()
    {
        if ($this->question->subtype === 'short') {
            return view('livewire.questions.question.open-question');
        }

        return view('livewire.questions.question.open-medium-question');
    }
}
