<?php

namespace tcCore\Http\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\School;
use tcCore\Traits\CanLogout;

class SchoolsGrid extends Component
{
    use WithPagination;
    use CanLogout;

    protected $schools;
    public $orderByColumnName = 'id';
    public $orderByDirection = 'desc';
    public $filters = [];
    public bool $administrator;

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
        if (session()->has('schools-grid-filters'))
            $this->filters = session()->get('schools-grid-filters');
        else {
            $this->filters = [
                'combined_admin_grid_search' => '',
            ];
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
        return CakeRedirectHelper::redirectToCake('school.new');
    }

    public function viewSchool($uuid)
    {
        return CakeRedirectHelper::redirectToCake('school.view', $uuid);
    }

    public function editSchool($uuid)
    {
        if (!$this->administrator) {
            return;
        }
        return CakeRedirectHelper::redirectToCake('school.edit', $uuid);
    }

    public function deleteSchool($uuid)
    {
        if (!$this->administrator) {
            return;
        }
        return CakeRedirectHelper::redirectToCake('school.delete', $uuid);
    }

    public function setOrderByColumnAndDirection($columnName)
    {
        if ($this->orderByColumnName === $columnName) {
            $this->orderByDirection = $this->orderByDirection == 'asc' ? 'desc' : 'asc';
            return;
        }
        $this->orderByColumnName = $columnName;
        $this->orderByDirection = 'asc';
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
