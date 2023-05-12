<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\School;
use tcCore\Traits\CanLogout;
use tcCore\Traits\CanOrderGrid;

class SchoolsGrid extends TCComponent
{
    use WithPagination;
    use CanLogout;
    use CanOrderGrid;

    protected $schools;
    public $filters = [];
    public bool $administrator;

    protected $listeners = [
        'school_deleted' => '$refresh',
    ];


    public function updatingAdministrator($value)
    {
        throw new \Exception('Manually updating the administrator property is not allowed.');
    }

    public function updatingFilters($value, $filter)
    {
        $this->resetPage();
    }

    public function updatedFilters($value, $filter)
    {
        session(['schools-grid-filters' => $this->filters]);
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

    private function setFilters()
    {
        if (session()->has('schools-grid-filters')) {
            $this->filters = session()->get('schools-grid-filters');
        } else {
            $this->clearFilters();
        }
    }

    private function cleanFilterForSearch(array $filters)
    {
        $searchFilter = [];
        foreach (['combined_admin_grid_search'] as $filter) {
            if (empty($filters[$filter])) {
                continue;
            }
            $searchFilter[$filter] = $filters[$filter];
        }
        return $searchFilter;
    }

    public function mount()
    {
        $this->administrator = Auth::user()->isA('administrator');
        $this->setFilters();
    }

    public function render()
    {
        $this->getFilteredAndSortedSchools();

        return view('livewire.schools-grid')
            ->layout('layouts.app-admin');
    }

    public function addNewSchool()
    {
        if ($this->administrator) {
            return CakeRedirectHelper::redirectToCake('school.new');
        }
    }

    public function viewSchool($uuid)
    {
        return CakeRedirectHelper::redirectToCake('school.view', $uuid, $this->page);
    }

    public function editSchool($uuid)
    {
        if ($this->administrator) {
            return CakeRedirectHelper::redirectToCake('school.edit', $uuid);
        }
    }

    public function deleteSchool($uuid)
    {
        if ($this->administrator) {
            $this->emit('openModal', 'school-delete-modal', ['schoolUuid' => $uuid]);
        }
    }

    protected function getFilteredAndSortedSchools()
    {
        $this->schools = School::filtered(
            $this->cleanFilterForSearch($this->filters),
            [$this->orderByColumnName => $this->orderByDirection]
        )->with('umbrellaOrganization')
            ->paginate(15, ['schools.*']);
    }
}
