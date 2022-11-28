<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use http\QueryString;
use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Period;
use tcCore\User;


abstract class AnalysesDashboard extends Component
{
    const FILTER_SESSION_KEY = 'STUDENT_ANALYSES_FILTER';

    public $educationLevelYears = [];

    public $periods = [];

    public $teachers = [];

    public $filters = [];

    public $title = '';

    public $taxonomyIdentifier;

    public $showEmptyStateForPValueGraph = false;

    public $dataValues = [];
    public $dataKeys = [];

    protected $topItems; //todo generate Top Items with a algorithm

    protected $taxonomies = [
        ['name' => 'Miller', 'height' => '150px'],
        ['name' => 'RTTI', 'height' => '150px'],
        ['name' => 'Bloom', 'height' => '200px'],
    ];

    protected $forUser;

    abstract public function getDataProperty();

    abstract public function render();

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

    protected function getMillerData($attainmentId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForAttainment($this->getUser(),
            $attainmentId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getRTTIData($attainmentId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForAttainment($this->getUser(),
            $attainmentId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getBloomData($attainmentId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForAttainment(
            $this->getUser(),
            $attainmentId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    public function getDataForGeneralGraph($subjectId, $taxonomy)
    {
        switch ($taxonomy) {
            case 'Miller':
                $data = $this->getMillerGeneralGraphData($subjectId);
                break;
            case 'RTTI':
                $data = $this->getRTTIGeneralGraphData($subjectId);
                break;
            case 'Bloom':
                $data = $this->getBloomGeneralGraphData($subjectId);
                break;
        }

        return [
            $showEmptyState = collect($data)->filter(fn($item) => $item[1] > 0)->isEmpty(),
            $this->transformForGraph($data)
        ];
    }


    protected function getMillerGeneralGraphData($subjectId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForAttainment($this->getUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getRTTIGeneralGraphData($subjectId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForAttainment($this->getUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    protected function getBloomGeneralGraphData($subjectId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForAttainment(
            $this->getUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues());
    }

    private function transformForGraph($data)
    {
        return collect($data)->map(function ($item) {
            return [
                'x'       => $item[0],
                'value'   => $item[1],
                'tooltip' => trans_choice(
                    'student.tooltip_taxonomy_graph',
                    $item[2], [
                    'count_questions' => $item[2],
                    'p_value'         => number_format($item[1], 2),
                ])
            ];
        });
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
        $this->periods = $this->getUser()->schoolLocation->getPeriods()
            ->map(fn($period) => [
                'value' => $period->id,
                'label' => $period->name,
            ]);

        $this->teachers = User::teachersForStudent($this->getUser())
            ->get()
            ->map(
                function ($teacher) {
                    return [
                        'value' => $teacher->id,
                        'label' => $teacher->name_full,
                    ];
                }
            );
        $this->educationLevelYears = EducationLevel::yearsForStudent($this->getUser())
            ->map(
                function ($year) {
                    return [
                        'value' => $year,
                        'label' => (string) $year,
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

    public function getFirstActiveForGeneralGraphTaxonomy()
    {
        foreach ($this->taxonomies as $key => $taxonomy) {
            $data = $this->getDataForGeneralGraph($this->taxonomyIdentifier, $taxonomy['name']);
            if (!$data[0]) {
                return $key;
            }
        }
        return false;
    }

    protected function getUser()
    {
        if (!$this->forUser) {
            if (auth()->user()->isA('teacher') && request('student_uuid')) {
                $this->forUser = User::whereUuid(request('student_uuid'))->first();
            } else {
                $this->forUser = auth()->user();
            }
        }

        return $this->forUser;
    }
}
