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
        return $this->forwardToProvider('retrieveWords') ?? [];
    }

    public function makeUpdates($updates): void
    {
        $this->forwardToProvider('makeUpdates', $updates);
    }

    public function openCompileListsModal(): void
    {
        $this->forwardToProvider('openCompileListsModal');
    }

    public function openViewWordListChangesModal(): void
    {
        $this->forwardToProvider('openViewWordListChangesModal');
    }

    private function forwardToProvider(string $method, $args = null): mixed
    {
        if ($this->relationGuard()) {
            return null;
        }

        return $this->obj->$method($args);
    }

    private function relationGuard(): bool
    {
        return !($this->obj instanceof Relation);
    }
}