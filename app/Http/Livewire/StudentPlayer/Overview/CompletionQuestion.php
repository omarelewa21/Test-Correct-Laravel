<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithStudentPlayerOverview;
use tcCore\Http\Livewire\StudentPlayer\CompletionQuestion as AbstractCompletionQuestion;

class CompletionQuestion extends AbstractCompletionQuestion
{
    use WithGroups;
    use WithStudentPlayerOverview;

    public $answered;
    public $searchPattern = "/\[([0-9]+)\]/i";


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
