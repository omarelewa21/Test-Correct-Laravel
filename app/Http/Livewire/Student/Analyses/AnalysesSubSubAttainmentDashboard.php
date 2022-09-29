<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use Livewire\Component;
use tcCore\Attainment;
use tcCore\EducationLevel;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Period;
use tcCore\Subject;
use tcCore\User;

class AnalysesSubSubAttainmentDashboard extends Component
{
    public $subject;

    protected $queryString = ['subject'];

    public $attainment;

    public $educationLevelYears = [];

    public $periods = [];

    public $teachers = [];

    public $filters = [];

    public function hasActiveFilters()
    {
        return collect($this->filters)->flatten()->isNotEmpty();
    }

    public function mount(?Attainment $attainment = null)
    {
        $this->attainment = $attainment;
        $this->clearFilters();
        $this->getFilterOptionsData();
    }

    public function render()
    {
        $this->dispatchBrowserEvent('filters-updated');
        return view('livewire.student.analyses.analyses-sub-sub-attainment-dashboard')->layout('layouts.student');
    }

    public function clearFilters()
    {
        $this->filters = [
            'educationLevelYears' => [],
            'periods'             => [],
            'teachers'            => [],
        ];
    }

    /**
     * @return void
     */
    public function getFilterOptionsData(): void
    {
        $this->periods = auth()->user()->schoolLocation->getPeriods()
            ->map(fn($period) => [
                'value' => $period->id,
                'label' => $period->name,
            ]);

        $this->teachers = User::teachersForStudent(auth()->user())
            ->get()
            ->map(
                function ($teacher) {
                    return [
                        'value' => $teacher->id,
                        'label' => $teacher->name_full,
                    ];
                }
            );
        $this->educationLevelYears = EducationLevel::yearsForStudent(auth()->user())
            ->map(
                function ($year) {
                    return [
                        'value' => $year,
                        'label' => $year,
                    ];
                }
            );
    }

    public function redirectBack()
    {
        return redirect(route('student.analyses.attainment.show', [
            'attainment' => $this->attainment->attainment->uuid,
            'subject'    => $this->subject,
        ]));
    }

}
