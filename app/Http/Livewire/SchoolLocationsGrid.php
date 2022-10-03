<?php

namespace tcCore\Http\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\School;
use tcCore\SchoolLocation;

class SchoolLocationsGrid extends Component
{
    use WithPagination;

    protected $schoolLocations;
    public $orderByColumnName = 'id';
    public $orderByDirection = 'desc';
    public $filters = [];
    public bool $administrator;

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
        if (session()->has('school-locations-grid-filters'))
            $this->filters = session()->get('school-locations-grid-filters');
        else {
            $this->filters = [
                'combined_search' => '',
                'license_type'    => [],
                'lvs_active'      => [],
                'sso_active'      => [],
            ];
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
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => '/school_locations/',
                'page_action' => "Loading.show();Popup.load('/school_locations/add', 1100);",
            ],
        ]);

        return $this->redirect($controller->toCakeUrl($request));
    }

    public function viewSchoolLocation($uuid)
    {
        $cakeAddress = sprintf('/school_locations/view/%s', $uuid);

        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => '/',
                'page_action' => "Navigation.load('$cakeAddress')"
            ]
        ]);

        return $this->redirect($controller->toCakeUrl($request));
    }

    public function editSchoolLocation($uuid)
    {
        if (!$this->administrator) {
            return;
        }
        $cakeAddress = sprintf('/school_locations/edit/%s', $uuid);

        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => '/',
                'page_action' => "Navigation.load('$cakeAddress')"
            ]
        ]);

        return $this->redirect($controller->toCakeUrl($request));
    }

    public function deleteSchoolLocation($uuid)
    {
        if (!$this->administrator) {
            return;
        }

        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => '/school_locations/',
                'page_action' => "SchoolLocation.delete('$uuid')"
            ]
        ]);

        return $this->redirect($controller->toCakeUrl($request));
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

    protected function getFilteredAndSortedSchoolLocations()
    {
        $this->schoolLocations = SchoolLocation::filtered(
            $this->cleanFilterForSearch($this->filters),
            [$this->orderByColumnName => $this->orderByDirection]
        )->with('school')
            ->paginate(15, ['school_locations.*']);
    }
}
