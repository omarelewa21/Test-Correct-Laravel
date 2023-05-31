<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

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
    use WithAttachments;
    use withCloseable;
    use WithGroups;
    use WithNotepad;

    public $answer = '';
    public $question;
    public $number;
    public $answers;
    public $editorId;
    public $testTakeUuid;
    public bool $allowWsc = false;

    public function mount()
    {
        $this->editorId = 'editor-'.$this->question->id;

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
        return view('livewire.student-player.question.open-question');
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
        return TestTake::whereUuid($this->testTakeUuid)->value('allow_wsc') && $this->question->isWritingAssignmentWithSpellCheckAvailable();
    }
}
