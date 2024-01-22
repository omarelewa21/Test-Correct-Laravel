<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Relation;
use tcCore\Http\Livewire\Teacher\Cms\Providers\TypeProvider;
use tcCore\Http\Requests\Request;
use tcCore\TestParticipant;

trait WithRelationQuestionBlocks
{
    public bool $showRelationQuestionWarning = false;

    public function handleRelationQuestionWarning(): void
    {
        if (!$this->showRelationQuestionWarning) {
            return;
        }

        $this->emit('openModal', 'teacher.test-take.relation-question-no-access-modal');;
        $this->showRelationQuestionWarning = false;
    }
}