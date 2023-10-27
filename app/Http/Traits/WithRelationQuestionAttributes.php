<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Relation;
use tcCore\Http\Livewire\Teacher\Cms\Providers\TypeProvider;
use tcCore\Http\Requests\Request;
use tcCore\TestParticipant;

trait WithRelationQuestionAttributes
{
    public function retrieveWords(): array
    {
        if ($this->relationGuard()) {
            return [];
        }

        return $this->obj->retrieveWords() ?? [];
    }

    public function makeUpdates($updates): void
    {
        if ($this->relationGuard()) {
            return;
        }

        $this->obj->makeUpdates($updates);
    }

    public function openCompileListsModal(): void
    {
        if ($this->relationGuard()) {
            return;
        }

        $this->obj->openCompileListsModal();
    }

    private function relationGuard(): bool
    {
        if(!($this->obj instanceof TypeProvider)) {
            return true;
        }
        return !($this->obj instanceof Relation);
    }
}