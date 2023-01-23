<?php

namespace tcCore\Http\Livewire\Analyses;

use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\LearningGoal;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Period;
use tcCore\User;


abstract class AnalysesDashboard extends Component
{
    const FILTER_SESSION_KEY = 'STUDENT_ANALYSES_FILTER';

    public $classUuid;
    public $studentUuid;

    protected $helper;

    public $educationLevelYears = [];

    public $periods = [];

    public $teachers = [];

    public $filters = [];

    public $title = '';

    public $taxonomyIdentifier;

    public $showEmptyStateForPValueGraph = false;

    public $dataValues = [];

    public $dataKeys = [];


//    public $topItems = [];

    public $displayRankingPanel = true;

    protected $taxonomies = [
        ['name' => 'Miller', 'height' => '170px'],
        ['name' => 'RTTI', 'height' => '170px'],
        ['name' => 'Bloom', 'height' => '200px'],
    ];

    private $_education_level_years_by_filter_values = null;
    private $_teachers_by_filter_values = null;
    private $_periods_by_filter_values = null;

    protected $forUser;

    abstract public function getDataProperty();

    abstract public function getTopItemsProperty();

    abstract public function render();

    public function mount()
    {
        $this->studentUuid = request('student_uuid');
        $this->classUuid = request('class_uuid');

        $this->setFilters();
        $this->getFilterOptionsData();
        $this->getDataProperty();
    }

    public function updatedFilters()
    {
        session([self::FILTER_SESSION_KEY => $this->filters]);
        $this->dispatchBrowserEvent('filters-updated');
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
                $data = $this->getMillerData($subjectId);
                break;
            case 'RTTI':
                $data = $this->getRTTIData($subjectId);
                break;
            case 'Bloom':
                $data = $this->getBloomData($subjectId);
                break;
            default:
                abort(403);
                break;
        }
        return [
            $showEmptyState = collect($data)->filter(fn($item) => $item[1] > 0)->isEmpty(),
            $this->transformForGraph($data)
        ];
    }

    protected function getMillerData($attainmentId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForAttainment(
            $this->getHelper()->getForUser(),
            $attainmentId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues(),
            $this->getIsLearningGoalFilter()
        );
    }

    protected function getIsLearningGoalFilter()
    {
        if (!property_exists($this, 'attainmentMode')) {
            return null;
        }

        if ($this->attainmentMode === LearningGoal::TYPE) {
            return 1;
        }

        return 0;
    }

    protected function getRTTIData($attainmentId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForAttainment(
            $this->getHelper()->getForUser(),
            $attainmentId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues(),
            $this->getIsLearningGoalFilter()
        );
    }

    protected function getBloomData($attainmentId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForAttainment(
            $this->getHelper()->getForUser(),
            $attainmentId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues(),
            $this->getIsLearningGoalFilter()
        );
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
            default:
                abort(403);
                break;
        }

        return [
            $showEmptyState = collect($data)->filter(fn($item) => $item[1] > 0)->isEmpty(),
            $this->transformForGraph($data)
        ];
    }


    protected function getMillerGeneralGraphData($subjectId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForAttainment($this->getHelper()->getForUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues(),
            $this->getIsLearningGoalFilter()
        );
    }

    protected function getRTTIGeneralGraphData($subjectId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForAttainment($this->getHelper()->getForUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues(),
            $this->getIsLearningGoalFilter()
        );
    }

    protected function getBloomGeneralGraphData($subjectId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForAttainment(
            $this->getHelper()->getForUser(),
            $subjectId,
            $this->getPeriodsByFilterValues(),
            $this->getEducationLevelYearsByFilterValues(),
            $this->getTeachersByFilterValues(),
            $this->getIsLearningGoalFilter()
        );
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

        $this->emit('filter-cleared');
        $this->dispatchBrowserEvent('filters-updated');

        session([self::FILTER_SESSION_KEY => $this->filters]);
    }

    /**
     * @return void
     */
    public function getFilterOptionsData(): void
    {
        $this->periods = $this->getHelper()->getForUser()->schoolLocation->getPeriods()
            ->map(fn($period) => [
                'value' => $period->id,
                'label' => $period->name,
            ]);

        $this->teachers = User::teachersForStudent($this->getHelper()->getForUser())
            ->get()
            ->map(
                function ($teacher) {
                    return [
                        'value' => $teacher->id,
                        'label' => $teacher->name_full,
                    ];
                }
            );
        $this->educationLevelYears = EducationLevel::yearsForStudent($this->getHelper()->getForUser())
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
        return $this->_periods_by_filter_values ??= Period::whereIn('id', $this->filters['periods'])->get('id');
    }

    protected function getEducationLevelYearsByFilterValues()
    {
        return $this->_education_level_years_by_filter_values ??= collect($this->filters['educationLevelYears'])->map(fn($levelYear) => ['id' => $levelYear]);
    }

    protected function getTeachersByFilterValues()
    {
        return $this->_teachers_by_filter_values ??= User::whereIn('id', $this->filters['teachers'])->get('id');
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

    public function getFirstActiveForRankingTaxonomy($modelId)
    {
        foreach ($this->taxonomies as $key => $taxonomy) {
            $data = $this->getData($modelId, $taxonomy['name']);
            if (!$data[0]) {
                return $key;
            }
        }
        return false;
    }

    protected function getHelper()
    {
        if (!$this->helper) {
            if (auth()->user()->isA('teacher')) {
                $this->helper = new AnalysesForTeacherHelper($this->studentUuid, $this->classUuid);
            } else {
                $this->helper = new AnalysesForStudentHelper();
            }
        }
        return $this->helper;
    }

    public function updatingClassUuid()
    {
        abort(403);
    }

    public function updatingStudentUuid()
    {
        abort(403);
    }

    public function viewingAsTeacher()
    {
        return auth()->user()->getKey() !== $this->getHelper()->getForUser()->getKey();
    }

    public function showGrades()
    {
        return redirect(
            $this->getHelper()->getRouteForShowGrades()
        );
    }

}
