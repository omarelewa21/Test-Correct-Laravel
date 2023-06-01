<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Answer;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\TestTake;

abstract class OpenQuestion extends TCComponent
{
    use withCloseable;

    public $answer = '';
    public $question;
    public $number;
    public $answers;
    public $editorId;
    public bool $allowWsc = false;


    public function mount()
    {
        $this->editorId = 'editor-'.$this->question->id;
        $this->allowWsc = $this->allowSpellChecker();
    }

    private function allowSpellChecker(): bool
    {
        return $this->question->isWritingAssignmentWithSpellCheckAvailable();
    }

    /**
     * @return void
     */
    protected function setAnswer(): void
    {
        $temp = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        if (key_exists('value', $temp)) {
            $this->answer = BaseHelper::transformHtmlCharsReverse($temp['value'], false);
        }
    }
}
