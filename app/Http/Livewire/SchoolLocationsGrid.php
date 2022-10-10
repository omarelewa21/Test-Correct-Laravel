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
use tcCore\SchoolLocation;
use tcCore\Traits\CanLogout;
use tcCore\Traits\CanOrderGrid;

class SchoolLocationsGrid extends Component
{
    use WithPagination;
    use CanLogout;
    use CanOrderGrid;

    protected $schoolLocations;

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
        session(['school-locations-grid-filters' => $this->filters]);
    }

    public function clearFilters()
    {
        $this->filters = [
            'combined_search' => '',
            'license_type'    => [],
            'lvs_active'      => [],
            'sso_active'      => [],
        ];
        session(['school-locations-grid-filters' => $this->filters]);
    }

    public function hasActiveFilters(): bool
    {
        return collect($this->filters)->reject(fn($item) => empty($item))->isNotEmpty();
    }

    private function setFilters()
    {
        if (session()->has('school-locations-grid-filters')) {
            $this->filters = session()->get('school-locations-grid-filters');
        } else {
            $this->clearFilters();
        }
    }

    private function cleanFilterForSearch(array $filters)
    {
        $searchFilter = [];
        foreach (['combined_search', 'license_type', 'lvs_active', 'sso_active'] as $filter) {
            if (empty($filters[$filter])) {
                continue;
            }
            $searchFilter[$filter] = $filters[$filter];
        }
        return $searchFilter;
    }

    public function getLicenseTypesProperty()
    {
        return collect(['client', 'trial'])
            ->map(function ($option, $key) {
                return ['value' => Str::upper($option) /*$key*/, 'label' => __('school_location.' . Str::upper($option))];
            })->toArray();
    }

    public function getYesOrNoProperty()
    {
        return collect(['no', 'yes'])
            ->map(function ($option, $key) {
                return ['value' => $key, 'label' => __("general.$option")];
            })->toArray();
    }

    public function mount()
    {
        $this->administrator = Auth::user()->isA('administrator');
        $this->setFilters();
    }

    public function render()
    {
        $this->getFilteredAndSortedSchoolLocations();

        return view('livewire.school-locations-grid')
            ->layout('layouts.app-admin');
    }

    public function addNewSchoolLocation()
    {
        return CakeRedirectHelper::redirectToCake('school_location.new');
    }

    public function viewSchoolLocation($uuid)
    {
        return CakeRedirectHelper::redirectToCake('school_location.view', $uuid);
    }

    public function editSchoolLocation($uuid)
    {
        if ($this->administrator) {
            return CakeRedirectHelper::redirectToCake('school_location.edit', $uuid);
        }
    }

    public function deleteSchoolLocation($uuid)
    {
        if ($this->administrator) {
            $this->emit('openModal', 'school-location-delete-modal', ['schoolLocationUuid' => $uuid]);
            return;
            return CakeRedirectHelper::redirectToCake('school_location.delete', $uuid);
        }
    }



    protected function getFilteredAndSortedSchoolLocations()
    {
        $this->schoolLocations = SchoolLocation::filtered(
            $this->cleanFilterForSearch($this->filters),
            [$this->orderByColumnName => $this->orderByDirection]
        )->with('school')
            ->paginate(15, ['school_locations.*']);
    }
}
