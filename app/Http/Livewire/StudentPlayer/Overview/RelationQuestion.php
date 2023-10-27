<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Livewire\StudentPlayer\RelationQuestion as AbstractRelationQuestion;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithStudentPlayerOverview;

class RelationQuestion extends AbstractRelationQuestion
{
    use WithGroups;
    use WithStudentPlayerOverview;

    public function render()
    {
        return view('livewire.student-player.overview.relation-question');
    }

}