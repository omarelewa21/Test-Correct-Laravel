<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use Livewire\WithPagination;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestTakeOverview extends Component
{
    use WithPagination;

    const STAGES = [
        TestTakeStatus::STATUS_TAKEN => 'taken',
    ];
    const TABS = ['taken', 'norm'];
    const PER_PAGE = 12;

    public string $stage;
    public $openTab = 'taken';
    public $filters = [];

    protected $queryString = ['openTab'];

    public function mount($stage)
    {
        if (!collect(self::STAGES)->contains($stage)) {
            abort(404);
        }
        if (!in_array($this->openTab, self::TABS)) {
            abort(404);
        }
        $this->setFilters();
        $this->stage = $stage;
    }

    public function render()
    {
        return view('livewire.teacher.test-take-overview')->layout('layouts.app-teacher');
    }

    public function getTakenTestTakesProperty()
    {
        return $this->testTakes->paginate(self::PER_PAGE);
    }

    public function getTestTakesProperty()
    {
        $filters = [
            'test_take_status_id' => [6, 7],
            'archived'            => 0
        ];
        $order = [
            'time_start' => 'desc'
        ];
        return TestTake::filtered($filters, $order)
            ->withCardAttributes();
    }

    public function getSchoolClassesProperty()
    {
        return $this->testTakes->get()->flatMap(function ($take) {
            return $take->schoolClasses()
                ->withoutGuestClasses()
                ->distinct()
                ->get()
                ->map(function ($class) {
                    return ['value' => $class->getKey(), 'label' => $class->name];
                });
        })->unique()->values()->toArray();
    }

    public function setFilters()
    {
        $this->filters['taken'] = [
            'name'    => '',
            'classes' => [],
        ];
    }
}