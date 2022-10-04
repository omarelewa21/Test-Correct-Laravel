<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\Http\Traits\WithTestTakeInteractions;
use tcCore\Subject;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestTakeOverview extends Component
{
    use WithPagination, WithTestTakeInteractions;

    const STAGES = [
        'planned' => [TestTakeStatus::STATUS_PLANNED],
        'taken'   => [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING],
        'norm'    => [TestTakeStatus::STATUS_DISCUSSED],
        'review'  => [TestTakeStatus::STATUS_RATED],
    ];
    const TABS = ['taken', 'norm'];
    const PER_PAGE = 12;
    const ACTIVE_TAB_SESSION_KEY = 'test-take-overview-open-tab';
    const DEFAULT_OPEN_TAB = 'taken';

    public string $stage;
    public $openTab = self::DEFAULT_OPEN_TAB;
    public $filters = [];

    protected $queryString = ['openTab'];

    /* Component lifecycle hooks */
    public function mount($stage)
    {
        $this->abortIfUnauthorized($stage);
        $this->setOpenTab();
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
    }

    public function updatedOpenTab()
    {
        $this->resetPage();
        session()->put(self::ACTIVE_TAB_SESSION_KEY, $this->openTab);
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
        return $this->filters[$tab ?? $this->openTab] = [
            'test_take_status_id' => $this->getTestTakeStatusForFilter($tab),
            'archived'            => false,
            'test_name'           => '',
            'school_class_id'     => [],
            'subject_id'          => [],
            'time_start_from'     => '',
            'time_start_to'       => '',
        ];
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

    public function getSchoolClassesWithoutGuestClasses()
    {
        return $this->schoolClasses->reject(fn($class) => $class->label === __('school_classes.guest_accounts'))->toArray();
    }

    private function setOpenTab()
    {
        $this->openTab = session(self::ACTIVE_TAB_SESSION_KEY, self::DEFAULT_OPEN_TAB);
    }
    /* End Helper methods */
}