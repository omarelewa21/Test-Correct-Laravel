<?php

namespace tcCore\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\SchoolLocation;

class SchoolLocationsGrid extends Component
{
    use WithPagination;

    protected $schoolLocations;
    public $orderByColumnName = 'id';
    public $orderByDirection = 'desc';
    public $filters = [];
    public $columns = [ //mainly for documentation
        'school_locations.customer_code',
        'school_locations.name',
        'schools.name',
        'school_locations.main_city',
        'school_locations.external_main_code',
        'school_locations.lvs_type',
        'school_locations.sso_type',
        'school_locations.count_active_licenses',
        'school_locations.count_students',
        'school_locations.count_questions',
    ];

    public function mount()
    {
        //
    }

    public function render()
    {
        $this->getFilteredAndSortedSchoolLocations();

        return view('livewire.school-locations-grid')
            ->layout('layouts.app-admin');
    }

    public function addNewSchoolLocation()
    {
        $this->filters = ['name' => 'TBNI'];
        //todo link to cake
        // Popup.load('/school_locations/add', 1100);
    }

    public function setOrderByColumnAndDirection($columnName)
    {
        if ($this->orderByColumnName === $columnName)
        {
            $this->orderByDirection = $this->orderByDirection == 'asc' ? 'desc' : 'asc';
            return;
        }
        $this->orderByColumnName = $columnName;
        $this->orderByDirection = 'asc';
    }

    protected function getFilteredAndSortedSchoolLocations()
    {
        $this->clearEmptyFilters();

        $this->schoolLocations = SchoolLocation::filtered($this->filters, [])
            ->with('school')
            ->when($this->orderByColumnName, function ($query, $value) {
                if($value == 'schools.name' || isset($this->filters['school_name'])){
                    return $query->join('schools as schools', 'school_locations.school_id', '=', 'schools.id');
                }
            })
            ->orderBy($this->orderByColumnName, $this->orderByDirection)
            //filtered doesnt allow ordering by other columns than id and name.
            // keep this orderBy statement or change it inside school_location filtered..
            ->paginate(15, ['school_locations.*']);

        //add abstract search mechanism to the query
    }

    public function clearEmptyFilters()
    {
        $this->filters = collect($this->filters)->filter(fn ($value) => $value)->toArray();
    }
}
