<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Livewire\StudentPlayer\RankingQuestion as AbstractRankingQuestion;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithStudentPlayerOverview;

class RankingQuestion extends AbstractRankingQuestion
{
    use WithGroups;
    use WithStudentPlayerOverview;
    use WithAttachments;

    public $answered;

    public function mount()
    {
        parent::mount();
    }

    public function render()
    {
        return view('livewire.student-player.overview.ranking-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}
