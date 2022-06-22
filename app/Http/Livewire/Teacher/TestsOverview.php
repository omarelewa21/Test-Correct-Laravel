<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\EducationLevel;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Http\Controllers\SubjectsController;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Requests\DuplicateTestRequest;
use tcCore\Subject;
use tcCore\Test;

class TestsOverview extends Component
{
    use WithPagination;

    const PER_PAGE = 12;


    public $filters = [];


    private $sorting = ['id' => 'desc'];

    protected $queryString = ['openTab'];

    public $openTab = 'personal';

    public $selected = [];

    protected $listeners = [
        'test-deleted' => '$refresh',
        'test-added'   => '$refresh',
    ];

    private $allowedTabs = [
        'school',
        'exams',
        'cito',
        'national',
        'personal',
    ];


    public function render()
    {
        $results = $this->getDatasource();

        return view('livewire.teacher.tests-overview')->layout('layouts.app-teacher')->with(compact(['results']));
    }

    public function updatingFilters($value, $filter)
    {
        $this->resetPage();
    }

    public function updatingOpenTab($value)
    {
        $this->resetPage();
    }

    private function getDatasource()
    {
        try { // added for compatibility with mariadb
            \DB::select(\DB::raw("set session optimizer_switch='condition_fanout_filter=off';"));
        } catch (\Exception $e) {
        }

        switch ($this->openTab) {
            case 'school':
                $datasource = $this->getSchoolDatasource();
                break;
            case 'exams':
                $datasource = $this->getExamsDatasource();
                break;
            case 'cito':
                $datasource = $this->getCitoDataSource();
                break;
            case 'national':
            case 'personal':
            default :
                $datasource = $this->getPersonalDatasource();
                break;

        }
        return $datasource;
    }

    private function getSchoolDatasource()
    {
        return Test::filtered(
            array_merge(
                $this->cleanFilterForSearch($this->filters['school']),
                ['owner_id' => auth()->user()->school_location_id]
            ),
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(self::PER_PAGE);

    }

    private function getExamsDatasource()
    {
        return Test::examFiltered(
            $this->cleanFilterForSearch($this->filters['exams']),
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(self::PER_PAGE);

    }

    private function getPersonalDatasource()
    {
        $this->filters['personal']['author_id'] = auth()->id();

        $results = Test::filtered(
            $this->cleanFilterForSearch($this->filters['personal']),
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->where('tests.author_id', auth()->user()->id)
            ->paginate(self::PER_PAGE);


        return $results;
    }

    private function getCitoDataSource()
    {
        $results = Test::citoFiltered(
            $this->cleanFilterForSearch($this->filters['cito']),
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(self::PER_PAGE);

        return $results;
    }

    private function setFilters()
    {
        collect($this->allowedTabs)->each(function ($tab) {
            $this->filters[$tab] = [
                'name'                 => '',
                'education_level_year' => [],
                'education_level_id'   => [],
                'subject_id'           => [],
                'author_id'            => [],
            ];
        });


        /** @TODO default search filter for teacher (is dirty now) */
//        $this->filters = array_merge($this->filters, auth()->user()->getSearchFilterDefaultsTeacher());
    }

    public function duplicateTest($testUuid)
    {
        // @TODO only duplicate when allowed?

        $test = Test::whereUuid($testUuid)->first();
        if ($test == null) {
            return 'Error no test was found';
        }

        if (!$test->canCopy(auth()->user())) {
            return 'Error duplication not allowed';
        }


        try {
            $newTest = $test->userDuplicate([], Auth::id());
        } catch (\Exception $e) {
            return 'Error duplication failed';
        }

        return __('general.duplication successful');
    }

    public function openEdit($testUuid)
    {
        $this->redirect(route('teacher.question-editor', [
            'testId'     => $testUuid,
            'action'     => 'edit',
            'owner'      => 'test',
            'withDrawer' => 'true',
            'referrer'   => 'teacher.tests',
        ]));
    }

    public function getTemporaryLoginToPdfForTest($testUuid)
    {
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => sprintf('/tests/view/%s', $testUuid),
                'page_action' => sprintf("Loading.show();Popup.load('/tests/pdf_showPDFAttachment/%s', 1000);", $testUuid),
            ],
        ]);

        return $controller->toCakeUrl($request);
    }


    public function getEducationLevelProperty()
    {
        return EducationLevel::filtered(['user_id' => auth()->id()], ['name' => 'desc'])
            ->get(['id', 'name'])
            ->map(function ($educationLevel) {
                return ['value' => (int)$educationLevel->id, 'label' => $educationLevel->name];
            });
    }

    public function getSubjectsProperty()
    {
        return Subject::when($this->openTab === 'cito', function ($query) {
            $query->citoFiltered([], ['name' => 'asc']);
        })->when($this->openTab === 'exams', function ($query) {
            $query->examFiltered([], ['name' => 'asc']);
        }, function ($query) {
            $query->filtered([], ['name' => 'asc']);
        })
            ->get(['name', 'id'])
            ->map(function ($subject) {
                return ['value' => (int)$subject->id, 'label' => $subject->name];
            })->toArray();
    }

    public function getEducationLevelYearProperty()
    {
        return collect(range(1, 6))->map(function ($item) {
            return ['value' => (int)$item, 'label' => (string)$item];
        })->toArray();
    }

    public function getAuthorsProperty()
    {
        return (new AuthorsController())->getBuilderWithAuthors()
            ->map(function ($author) {
                return ['value' => $author->id, 'label' => trim($author->name_first . ' ' . $author->name)];
            })->toArray();
    }

    public function mount()
    {
        if (auth()->user()->schoolLocation->allow_new_test_bank !== 1) {
            abort(403);
        }
        $this->setFilters();
    }

    private function cleanFilterForSearch(array $filters)
    {
        $searchFilter = [];
        foreach (['name', 'education_level_year', 'education_level_id', 'subject_id', 'author_id'] as $filter) {
            if (!empty($filters[$filter])) {
                $searchFilter[$filter] = $filters[$filter];
            }
        }
        return $searchFilter;
    }

    public function openTestDetail($testUuid) {
//        redirect()->to(route('teacher.test-detail', ['uuid' => $testUuid]));
    }
}
