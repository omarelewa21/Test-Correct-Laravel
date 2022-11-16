<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use Livewire\Component;
use tcCore\Attainment;
use tcCore\BaseAttainment;
use tcCore\EducationLevel;
use tcCore\Http\Traits\WithAnalysesGeneralData;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Period;
use tcCore\Subject;
use tcCore\User;

class AnalysesSubSubAttainmentDashboard extends Component
{
    use WithAnalysesGeneralData;

    const FILTER_SESSION_KEY = 'STUDENT_ANALYSES_FILTER';

    public $subject;

    protected $queryString = ['subject'];

    public $attainment;

    public $educationLevelYears = [];

    public $periods = [];

    public $teachers = [];

    public $filters = [];

    public $parentAttainment;

    public $parentParentAttainment;

    protected $taxonomies = [
        'Miller',
        'RTTI',
        'Bloom',
    ];

    public $taxonomyIdentifier;


    public function hasActiveFilters()
    {
        return collect($this->filters)->flatten()->isNotEmpty();
    }

    public function mount(?BaseAttainment $baseAttainment = null)
    {
        $this->attainment = $baseAttainment;
        $this->taxonomyIdentifier = $this->attainment->id;
        $this->parentAttainment = BaseAttainment::find($this->attainment->attainment_id);
        $this->parentParentAttainment = BaseAttainment::find($this->parentAttainment->attainment_id);

        $this->setFilters();
        $this->getFilterOptionsData();
    }

    public function updatedFilters()
    {
        session([self::FILTER_SESSION_KEY => $this->filters]);
    }

    private function setFilters()
    {
        session()->has(self::FILTER_SESSION_KEY)
            ? $this->filters = session()->get(self::FILTER_SESSION_KEY)
            : $this->clearFilters();
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

        session([self::FILTER_SESSION_KEY => $this->filters]);
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
                        'label' => (string)$year,
                    ];
                }
            );
    }

    public function getDataForGeneralGraph($subjectId, $taxonomy)
    {
        switch ($taxonomy) {
            case 'Miller':
                $data = $this->getMillerAttainmentData($subjectId);
                break;
            case 'RTTI':
                $data = $this->getRTTIAttainmentData($subjectId);
                break;
            case 'Bloom':
                $data = $this->getBloomAttainmentData($subjectId);
                break;
        }

            return [
                $showEmptyState = collect($data)->filter(fn($item) => $item[1] > 0)->isEmpty(),
                $data
            ];
    }

    public function redirectBack()
    {
        return redirect(
            route('student.analyses.subattainment.show', [
                    'baseAttainment' => $this->parentAttainment->uuid,
                    'subject'        => $this->subject
                ]
            )
        );
    }

    protected function getMillerAttainmentData($subjectId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForAttainment(auth()->user(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getRTTIAttainmentData($subjectId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForAttainment(auth()->user(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getBloomAttainmentData($subjectId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForAttainment(
            auth()->user(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getPeriodsByFilterValues()
    {
        return Period::whereIn('id', $this->filters['periods'])->get('id');
    }

    protected function getEducationLevelYearsByFilterValues()
    {
        return collect($this->filters['educationLevelYears'])->map(fn($levelYear) => ['id' => $levelYear]);
    }

    protected function getTeachersByFilterValues()
    {
        return User::whereIn('id', $this->filters['teachers'])->get('id');
    }
}
