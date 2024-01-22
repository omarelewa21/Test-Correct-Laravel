<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Livewire\StudentPlayer\OpenQuestion as AbstractOpenQuestion;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithStudentPlayerOverview;

class OpenQuestion extends AbstractOpenQuestion
{
    use WithGroups;
    use WithStudentPlayerOverview;
    use WithAttachments;

    public $answered;

    public function mount()
    {
        parent::mount();

        $this->setAnswer();
    }

    public function render()
    {
        return view('livewire.student-player.overview.open-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
