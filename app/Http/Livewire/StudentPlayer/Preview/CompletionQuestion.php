<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\CompletionQuestion as AbstractCompletionQuestion;

class CompletionQuestion extends AbstractCompletionQuestion
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;

    public function updatedAnswer($value, $field) {}

    protected function completionHelper($context = 'teacher-preview'): string
    {
        return parent::completionHelper($context);
    }

    protected function multiHelper($createOptionCallback = null)
    {
        return parent::multiHelper(function ($matches, $answers) {
            return sprintf(
                '<select wire:model="answer.%s" class="form-input text-base max-w-full overflow-ellipsis overflow-hidden" @change="$event.target.setAttribute(\'title\', $event.target.value);" selid="testtake-select">%s</select>',
                $matches[1],
                $this->getOptions($answers)
            );
        });
    }

    public function render()
    {
        return view('livewire.student-player.preview.completion-question', ['html' => $this->getHtml()]);
    }
}
