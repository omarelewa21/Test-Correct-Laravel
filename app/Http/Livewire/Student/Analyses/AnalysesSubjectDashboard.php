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

class AnalysesSubjectDashboard extends Component
{

    public $educationLevelYears = [];

    public $periods = [];

    public $teachers = [];

    public $filters = [];

    public $title = '';

    public $dataValues = [];
    public $dataKeys = [];

    private $topAttainments = [
        3 => 'Schrijfvaardigheid',
        5 => 'Literatuur',
        6 => 'Oriëntatie op studie en beroep',
    ];

    private $taxonomies = [
        'Miller',
        'RTTI',
        'Bloom',
    ];

    public function mount(Subject $subject)
    {
        $this->subject = $subject;

        $this->clearFilters();
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

        $this->getDataProperty();
    }

    public function render()
    {
        $this->dispatchBrowserEvent('filters-updated');
        return view('livewire.student.analyses.analyses-subject-dashboard')->layout('layouts.student');
    }

    public function getData($attainmentId, $taxonomy)
    {
        switch ($taxonomy) {
            case 'Miller':
                return $this->getMillerDataForAttainment($attainmentId);
                break;
            case 'RTTI':
                return $this->getRTTIDataForAttainment($attainmentId);
                break;
            case 'Bloom':
                return $this->getBloomDataForAttainment($attainmentId);
                break;
        }
        // abort(403);
    }


    public function getDataProperty()
    {
        $result = PValueRepository::getPValueForStudentBySubject(
            auth()->user(),
            Period::whereIn('id', $this->filters['periods'])->get('id'),
            collect($this->filters['educationLevelYears'])->map(fn($levelYear) => ['id' => $levelYear]),
            User::whereIn('id', $this->filters['teachers'])->get('id')
        );
        //($result->toArray());//;->mapWithKey(fn($value, $key) => [$value->subject => $value->score]));


        $this->dataValues = ($result->toArray());
//        $this->dataKeys = array_keys($result);
//
        return $result;
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
    }

    private function getMillerDataForAttainment($attainmentId)
    {
        return PValueTaxonomyMillerRepository::getPValueForStudentForAttainment(auth()->user(), $attainmentId);
    }

    private function getRTTIDataForAttainment($attainmentId)
    {
        return PValueTaxonomyRTTIRepository::getPValueForStudentForAttainment(auth()->user(), $attainmentId);
    }

    private function getBloomDataForAttainment($attainmentId)
    {
        return PValueTaxonomyBloomRepository::getPValueForStudentForAttainment(auth()->user(), $attainmentId);
    }
}
