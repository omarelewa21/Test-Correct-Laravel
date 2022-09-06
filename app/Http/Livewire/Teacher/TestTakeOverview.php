<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestTakeOverview extends Component
{
    use WithPagination;

    const STAGES = [
        'planned' => [TestTakeStatus::STATUS_PLANNED],
        'taken'  => [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING],
        'review' => [TestTakeStatus::STATUS_DISCUSSED, TestTakeStatus::STATUS_RATED],
    ];
    const TABS = ['taken', 'norm'];
    const PER_PAGE = 12;

    public string $stage;
    public $openTab = 'taken';
    public $filters = [];

    protected $queryString = ['openTab'];

    /* Component lifecycle hooks */
    public function mount($stage)
    {
        if (!collect(self::STAGES)->has($stage) || !collect(self::TABS)->contains($this->openTab)) {
            abort(404);
        }

        $this->setFilters();
        $this->stage = $stage;
    }

    public function render()
    {
        return view('livewire.teacher.test-take-overview')->layout('layouts.app-teacher');
    }

    public function updatingFilters(&$value, $name)
    {
        if (Str::contains($name, 'time_start_from') || Str::contains($name, 'time_start_to')) {
            $value = Carbon::parse($value)->format('Y-m-d');
        }
    }
    /* End Component lifecycle hooks */

    /* Computed properties */
    public function getTakenTestTakesProperty()
    {
        return $this->testTakes->paginate(self::PER_PAGE);
    }

    public function getTestTakesProperty()
    {
        return TestTake::filtered($this->getFilters(), ['time_start' => 'desc'])->withCardAttributes();
    }

    public function getBaseTakesProperty()
    {
        return TestTake::filtered([
            'test_take_status_id' => [6, 7],
            'archived'            => 0,
        ], [])
            ->get(['id', 'take_id']);
    }

    public function getSchoolClassesProperty()
    {
        return TestTake::schoolClassesForMultiple($this->baseTakes->pluck('id'))
            ->withoutGuestClasses()
            ->optionList();
    }

    public function getSubjectsProperty()
    {
        return Subject::select(['subjects.id', 'subjects.name'])
            ->join('tests', 'tests.subject_id', '=', 'subjects.id')
            ->whereIn('tests.id', $this->baseTakes->pluck('test_id'))
            ->distinct()
            ->optionList();
    }
    /* End Computed properties */

    /* Filter methods */
    private function getFilters()
    {
        return collect($this->filters[$this->openTab])->reject(function ($filter) {
            return empty($filter);
        });
    }

    private function setFilters()
    {
        $this->filters['taken'] = [
            'test_take_status_id' => [6, 7],
            'archived'            => 0,
            'name'                => '',
            'school_class_id'     => [],
            'subject_id'          => [],
            'time_start_from'     => '',
            'time_start_to'       => '',
        ];
    }

    public function hasActiveFilters()
    {
        return $this->getFilters()->except('test_take_status_id')->isNotEmpty();
    }

    public function clearFilters($tab = null)
    {
        $this->dispatchBrowserEvent('clear-flatpickr');
        return $this->setFilters();
    }
    /*  End Filter methods */
}