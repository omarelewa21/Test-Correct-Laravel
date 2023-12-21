<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Arr;

trait WithVersionableCmsHandling
{
    public bool $addable = false;
    public array $used = [];
    public function getListeners(): array
    {
        return $this->listeners + ['usedPropertiesUpdated'];
    }

    public function mountWithVersionableCmsHandling() {}

    public function addToUsed(int $id): void
    {
        $this->used[] = $id;
    }

    public function usedPropertiesUpdated($event): void
    {
        $this->handleUpdatedProperties($event);
    }

    abstract protected function handleUpdatedProperties(array $updates);
}