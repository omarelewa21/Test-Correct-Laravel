<?php

namespace tcCore\Http\Livewire\Student\Analyses;

use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Period;
use tcCore\User;
use function view;

class AnalysesDashboard extends Component
{
    public $educationLevelYears = [];

    public $periods = [];

    public $teachers = [];

    public $filters = [];

    public $title = '';

    public $dataValues = [];
    public $dataKeys = [];

    private $topSubjects = [
        1 => 'Biology',
        2 => 'Nederlands',
    ];

    private $taxonomies = [
        'Miller',
        'RTTI',
        'Bloom',
    ];

    public function getData($subjectId, $taxonomy)
    {
        switch ($taxonomy) {
            case 'Miller':
                return $this->getMillerDataForSubject($subjectId);
                break;
            case 'RTTI':
                return $this->getRTTIDataForSubject($subjectId);
                break;
            case 'Bloom':
                return $this->getBloomDataForSubject($subjectId);
                break;
        }
       // abort(403);
    }


    public function mount()
    {
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

    public function render()
    {
        $this->dispatchBrowserEvent('filters-updated');//, ['newName' => $value]);
        return view('livewire.student.analyses.analyses-dashboard')->layout('layouts.student');;
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

    private function getMillerDataForSubject($subjectId)
    {
        $data = PValueRepository::getPValueForStudentForSubjectMiller(auth()->user(), $subjectId);

        $return = [];
        foreach($data as $key => $value) {
            $return[] = [$key, $value];
        }
        return $return;

        return  [
            ['Reproductie', 0.39],
            ['Toepassen 1', 0.54],
            ['Toepassen 2', 0.2],
            ['Inzicht', 0.1],
        ];
    }

    private function getRTTIDataForSubject($subjectId)
    {
        return [
            ['Reproductie', 0.39],
            ['Toepassen 1', 0.54],
            ['Toepassen 2', 0.2],
            ['Inzicht', 0.1],
        ];
    }

    private function getBloomDataForSubject($subjectId)
    {
        return [
            ['Onthouden', 0.39],
            ['Begrijpen', 0.54],
            ['Toepassen', 0.2],
            ['Analyseren', 0.1],
            ['Evalueren', 0.1],
            ['Creeren', 0.1],
        ];
    }
}
