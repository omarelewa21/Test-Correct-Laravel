<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Helpers\BaseHelper;

abstract class OpenQuestion extends StudentPlayerQuestion
{
    public $answer = '';
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
