<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Period;
use tcCore\User;
use function view;

abstract class AnalysesDashboard extends Component
{
    const FILTER_SESSION_KEY = 'STUDENT_ANALYSES_FILTER';

    public $educationLevelYears = [];

    public $periods = [];

    public $teachers = [];

    public $filters = [];

    public $title = '';

    public $dataValues = [];
    public $dataKeys = [];

    protected $topItems; //todo generate Top Items with a algorithm

    protected $taxonomies = [
        'Miller',
        'RTTI',
        'Bloom',
    ];

    abstract public function getDataProperty();

    abstract public function render();

    abstract protected function getMillerData($modelId);

    abstract protected function getRTTIData($modelId);

    abstract protected function getBloomData($modelId);

    public function mount()
    {
        $this->setFilters();

        $this->getFilterOptionsData();

        $this->getDataProperty();
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

    public function getData($subjectId, $taxonomy)
    {
        switch ($taxonomy) {
            case 'Miller':
                return $this->getMillerData($subjectId);
                break;
            case 'RTTI':
                return $this->getRTTIData($subjectId);
                break;
            case 'Bloom':
                return $this->getBloomData($subjectId);
                break;
        }
        // abort(403);
    }

    public function hasActiveFilters()
    {
        return collect($this->filters)->flatten()->isNotEmpty();
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
