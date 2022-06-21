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

    const PER_PAGE = 1;

    public $search = '';

    public $filters = [
        'name'                 => '',
        'education_level_year' => [],
        'education_level_id'   => [],
        'subject_id'           => [],
        'authors_id'           => [],
    ];
    public $filters1 = [
        'name'                 => '',
        'education_level_year' => '',
        'education_level_id'   => '',
        'subject_id'           => '',
    ];
    public $sorting = [];

    protected $queryString = ['openTab'];

    public $openTab = 'personal';

    public $selected = [];

    protected $listeners = ['test-deleted' => '$refresh'];


    public function render()
    {
        $results = $this->getDatasource();

        return view('livewire.teacher.tests-overview')->layout('layouts.app-teacher')->with(compact(['results']));
    }

    public function updatingFilters($value, $filter)
    {
        $this->resetPage();
    }

    public function updatingOpenTab()
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
            $this->cleanFilterForSearch($this->filters),
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(self::PER_PAGE);

    }

    private function getExamsDatasource()
    {
        return Test::examFiltered(
            $this->cleanFilterForSearch($this->filters),
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(self::PER_PAGE);

    }

    private function getPersonalDatasource()
    {
        $results = Test::filtered(
            $this->cleanFilterForSearch($this->filters),
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
            $this->cleanFilterForSearch($this->filters),
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(self::PER_PAGE);


        return $results;
    }

    private function setFilters()
    {
        $this->filters = array_merge($this->filters, auth()->user()->getSearchFilterDefaultsTeacher());
    }

    public function duplicateTest($testUuid)
    {
        // @TODO only duplicate when allowed?

        $test = Test::whereUuid($testUuid)->first();
        if ($test == null) {
            return 'Error no test was found';
        }

        if (!$test->canDuplicate()) {
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
        $this->redirect(route('teacher.question-editor', ['testId' => $testUuid]));
    }

    public function getTemporaryLoginToPdfForTest()
    {
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options'  => [
                'page' => '/tests/view/608d93d7-07bd-4f7a-95ad-231c283ee452',
                'page_action' => "Loading.show();Popup.load('/tests/pdf_showPDFAttachment/608d93d7-07bd-4f7a-95ad-231c283ee452', 1000);"
            ],
        ]);

        return $controller->toCakeUrl($request);
    }


    public function getEducationLevelProperty()
    {
        return EducationLevel::filtered([], ['name' => 'desc'])
            ->select(['id', 'name'])
            ->get()
            ->map(function ($educationLevel) {
                return ['value' => (int) $educationLevel->id, 'label' => $educationLevel->name];
            });
    }

    public function getSubjectsProperty()
    {
        return Subject::filtered([], ['name' => 'asc'])
            ->select(['name', 'id'])
            ->get()
            ->map(function ($subject) {
                return ['value' => (int) $subject->id, 'label' => $subject->name];
            })->toArray();
    }

    public function getEducationLevelYearProperty()
    {
        return collect(range(1,6))->map(function($item) {
            return ['value' => (int) $item, 'label' => (string) $item];
        })->toArray();
    }

    public function getAuthorsProperty()
    {
        return (new AuthorsController())->getBuilderWithAuthors()
            ->map(function ($author) {
                return ['value' => $author->id, 'label' => trim($author->name_first . ' ' . $author->name)];
            })->toArray();
    }

    public function mount(){
        $this->setFilters();
    }

    private function cleanFilterForSearch(array $filters)
    {
        $searchFilter = [];
        foreach(['name', 'education_level_year', 'education_level_id', 'subject_id', 'authors_id'] as $filter) {
            if (!empty($filter)) {
                $searchFilter[$filter] = $filters[$filter];
            }
        }
        return $searchFilter;



    }


}
