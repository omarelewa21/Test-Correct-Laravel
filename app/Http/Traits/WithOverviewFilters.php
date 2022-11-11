<?php


namespace tcCore\Http\Traits;


trait WithOverviewFilters
{
    public function updatingFilters($value, $filter)
    {
        $this->resetPage();
    }

    public function updatedFilters($value, $filter)
    {
        session([$this::class.'-filters' => $this->filters]);
    }

    public function clearFilters()
    {
        $this->filters = [
            'combined_admin_grid_search' => '',
        ];
        session(['schools-grid-filters' => $this->filters]);
    }

    public function hasActiveFilters(): bool
    {
        return collect($this->filters)->reject(fn($item) => empty($item))->isNotEmpty();
    }
}