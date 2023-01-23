<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Str;
use tcCore\UserSystemSetting;

trait WithAddExistingQuestionFilterSync
{
    protected string $filterSessionKey = 'aeq-%s-filters';

    protected function getFilterSessionKey(): string
    {
        $identifier = $this->filterIdentifyingAttribute;
        return sprintf($this->filterSessionKey, $this->$identifier);
    }

    public function updatedWithAddExistingQuestionFilterSync($name, $value)
    {
        if (Str::startsWith($name, 'filters.')) {
            $this->notifySharedFilterComponents();
        }
    }

    public function loadSharedFilters(): void
    {
        $newFilters = UserSystemSetting::getSetting(
            user: auth()->user(),
            title: $this->getFilterSessionKey()
        );

        $this->setFilters($newFilters);
    }

    protected function notifySharedFilterComponents(): void
    {
        $this->emit('shared-filter-updated', ['except' => $this::class]);
    }
}