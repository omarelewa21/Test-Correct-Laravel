<?php

namespace tcCore\Http\Livewire\Question;

use tcCore\Answer;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\TestTake;

class OpenQuestion extends TCComponent
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups;

    public $answer = '';
    public $question;
    public $number;
    public $answers;
    public $editorId;
    public $testTakeUuid;
    public bool $allowWsc = false;

    public function mount()
    {
        $this->editorId = 'editor_'.$this->question->id;

        $temp = (array) json_decode($this->answers[$this->question->uuid]['answer']);
        if (key_exists('value', $temp)) {
            $this->answer = BaseHelper::transformHtmlCharsReverse($temp['value'], false);
        }

        $this->allowWsc = $this->allowSpellChecker();
//        $this->attachments = $this->question->attachments;
    }


    public function updatedAnswer($value)
    {
        $json = json_encode((object) ['value' => $this->cleanData($value)]);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

        $this->emitTo('question.navigation','current-question-answered', $this->number);
    }

    public function render()
    {
        if ($this->question->isSubType('short')) {
            return view('livewire.question.open-question');
        }

        return view('livewire.question.open-medium-question');
    }

    /**
     * filter answer value from xss and encode html entities
     * 
     * @param string $value
     * 
     * @return string
     */
    private function cleanData($value)
    {
        $value = clean($value);

        return $this->question->isSubType('short') ? strip_tags($value) : $value;
    }

    private function allowSpellChecker(): bool
    {
        $testTake = TestTake::whereUuid($this->testTakeUuid)->first();
        return $testTake->isAssignmentType() ? $testTake->allow_wsc : false;
    }
}
