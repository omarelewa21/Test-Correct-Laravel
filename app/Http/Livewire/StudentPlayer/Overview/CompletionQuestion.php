<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithStudentPlayerOverview;
use tcCore\Http\Livewire\StudentPlayer\CompletionQuestion as AbstractCompletionQuestion;
use tcCore\Http\Traits\WithAttachments;

class CompletionQuestion extends AbstractCompletionQuestion
{
    use WithGroups;
    use WithStudentPlayerOverview;
    use WithAttachments;

    public $answered;
    public $searchPattern = "/\[([0-9]+)\]/i";

    public function mount(): void
    {
        $this->answer = (array)json_decode($this->answers[$this->question->uuid]['answer']);
        parent::mount();
    }

    public function render()
    {
        return view('livewire.student-player.overview.completion-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        $tags = [];
        $this->question->completionQuestionAnswers->each(function ($answer) use (&$tags) {
            $tags[$answer->tag] = true;
        });
        return count($tags) === count(array_filter($this->answer));
    }
}
