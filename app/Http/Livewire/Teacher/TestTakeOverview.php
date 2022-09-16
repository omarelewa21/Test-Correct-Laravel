<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Subject;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestTakeOverview extends Component
{
    use WithPagination;

    const STAGES = [
        'planned' => [TestTakeStatus::STATUS_PLANNED],
        'taken'   => [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING],
        'norm'    => [TestTakeStatus::STATUS_DISCUSSED],
        'review'  => [TestTakeStatus::STATUS_RATED],
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
        $this->abortIfUnauthorized($stage);

        $this->setFilters();
        $this->stage = $stage;
    }

    public function render()
    {
        return view('livewire.teacher.test-take-overview')->layout('layouts.app-teacher');
    }

    public function updatingFilters(&$value, $name)
    {
        $this->resetPage();
        $value = $this->parseDateIfNecessary($name, $value);
    }

    public function updatedOpenTab()
    {
        $this->resetPage();
    }
    /* End Component lifecycle hooks */

    /* Computed properties */
    public function getTakenTestTakesProperty()
    {
        return $this->testTakes->paginate(self::PER_PAGE);
    }

    public function getTestTakesProperty()
    {
        return TestTake::filtered($this->getFilters(), ['time_start' => 'desc'])
            ->filterByArchived(['archived' => $this->getArchivedFilter()])
            ->withCardAttributes();
    }

    public function getBaseTakesProperty()
    {
        /* The user-unfiltered results to build subjects and schoolclasses filters with; */
        return TestTake::filtered([
            'test_take_status_id' => $this->getTestTakeStatusForFilter($this->openTab),
            'archived'            => $this->getArchivedFilter(),
        ], [])
            ->filterByArchived(['archived' => $this->getArchivedFilter()])
            ->get(['id', 'test_id']);
    }

    public function getSchoolClassesProperty()
    {
        return TestTake::schoolClassesForMultiple($this->baseTakes->pluck('id'))->optionList();
    }

    public function getSubjectsProperty()
    {
        return Subject::fromTests($this->baseTakes->pluck('test_id'))->optionList();
    }

    public function getTestTakesWithSchoolClassesProperty()
    {
        return $this->takenTestTakes->each(function ($take) {
            $classes = $take->testParticipants->map(function ($participant) {
                return $participant->school_class_id;
            })->unique();

            $take->schoolClasses = $this->schoolClasses->whereIn('value', $classes);
        });
    }
    /* End Computed properties */

    /* Filter methods */
    private function getFilters()
    {
        return collect($this->filters[$this->openTab])->reject(fn($filter) => empty($filter));
    }

    private function setFilters()
    {
        collect(self::TABS)->each(function ($tab) {
            $this->filters[$tab] = [
                'test_take_status_id' => $this->getTestTakeStatusForFilter($tab),
                'archived'            => false,
                'test_name'           => '',
                'school_class_id'     => [],
                'subject_id'          => [],
                'time_start_from'     => '',
                'time_start_to'       => '',
            ];
        });
    }

    public function hasActiveFilters()
    {
        return $this->getFilters()->except('test_take_status_id')->isNotEmpty();
    }

    public function clearFilters($tab = null)
    {
        $this->dispatchBrowserEvent('clear-datepicker');
        return $this->setFilters();
    }

    private function getTestTakeStatusForFilter($tab)
    {
        return self::STAGES[$tab] ?? [6];
    }

    private function getArchivedFilter(): int
    {
        return $this->filters[$this->openTab]['archived'] ? 1 : 0;
    }
    /*  End Filter methods */

    /*  Helper methods */
    /**
     * @param $stage
     * @return void
     */
    private function abortIfUnauthorized($stage): void
    {
        if (!collect(self::STAGES)->has($stage) || !collect(self::TABS)->contains($this->openTab)) {
            abort(404);
        }
    }

    /**
     * @param $name
     * @param $value
     * @return mixed|string
     */
    private function parseDateIfNecessary($name, $value)
    {
        if (Str::contains($name, 'time_start_from') || Str::contains($name, 'time_start_to')) {
            $value = Carbon::parse($value)->format('Y-m-d');
        }
        return $value;
    }

    public function getSchoolClassesWithoutGuestClasses()
    {
        return $this->schoolClasses->reject(fn($class) => $class->label === __('school_classes.guest_accounts'));
    }

    public function openTestTakeDetail($testTakeUuid)
    {
        return TestTake::redirectToDetailPage($testTakeUuid);
    }
    /* End Helper methods */
}