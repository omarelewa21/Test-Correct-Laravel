<?php

namespace tcCore\Http\Livewire\Overview;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

abstract class OverviewComponent extends Component
{
    use WithPagination;

    const PER_PAGE = 15;

    public array $filters = [];
    protected array $filterableAttributes = [];
    protected string $sessionKey = '';
    protected bool $storeFiltersInSession = true;

    public function mount()
    {
        $this->restoreFiltersFromSession();
    }

    public function updatingFilters($value, $filter)
    {
        $this->resetPage();
    }

    public function updatedFilters($value, $filter)
    {
        $this->updateFiltersInSession($this->filters);
    }

    public function updatedPage($value)
    {
        $this->updatePageInSession($value);
    }

    protected function setFilters(array $filters = null): void
    {
        $this->filters = $filters ?? $this->filterableAttributes;
    }

    public function hasActiveFilters(): bool
    {
        return !empty($this->getCleanFilterForSearch());
    }

    public function clearFilters(): void
    {
        $this->setFilters();
    }

    protected function getCleanFilterForSearch(): array
    {
        return collect($this->filters)->reject(fn($value) => blank($value))->toArray();
    }

    protected function getSessionKey(): string
    {
        if (filled($this->sessionKey)) return $this->sessionKey;

        return sprintf('%s-session', Str::kebab(class_basename(get_called_class())));
    }

    private function updateFiltersInSession(array $filters)
    {
        if ($this->storeFiltersInSession) {
            session()->put($this->getSessionKey() . '-filters', $filters);
        }
    }

    private function updatePageInSession(int $value)
    {
        if ($this->storeFiltersInSession) {
            session()->put($this->getSessionKey() . '-page', $value);
        }
    }

    private function restoreFiltersFromSession()
    {
        $sessionFilters = session()->get($this->getSessionKey() . '-filters', null);
        $this->setFilters($sessionFilters);
        if ($page = session()->get($this->getSessionKey() . '-page', null)) {
            $this->setPage($page);
        }
    }
}