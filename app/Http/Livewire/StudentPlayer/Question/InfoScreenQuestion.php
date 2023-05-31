<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Livewire\StudentPlayer\InfoScreenQuestion as AbstractInfoScreenQuestion;

class InfoScreenQuestion extends AbstractInfoScreenQuestion
{
    use WithAttachments;
    use WithGroups;
    use WithNotepad;

    public $answer = '';
    public $testTakeUuid;

    public function mount()
    {
        if ($this->answers[$this->question->uuid]['answered']) {
            $this->answer = 'seen';
        }
    }

    public function render()
    {
        return view('livewire.student-player.question.info-screen-question');
    }

    public function markAsSeen($questionUuid): void
    {
        Answer::updateJson($this->answers[$questionUuid]['id'], json_encode('seen'));
    }
}
