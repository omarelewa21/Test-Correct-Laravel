<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\EducationLevel;
use tcCore\Http\Requests\DuplicateTestRequest;
use tcCore\Test;

class TestsOverview extends Component
{
    use WithPagination;

    const PER_PAGE = 16;

    public $subjects = [];
    public $educationLevelYear = [];
    public $educationLevel = [];
    public $search = '';

    public $filters = [
        'name'                 => '',
        'education_level_year' => '',
        'education_level_id'   => '',
        'subject_id'           => '',
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
        $this->setFilters();
        return view('livewire.teacher.tests-overview')->with(compact(['results']));
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
            $this->filters,
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(self::PER_PAGE);

    }

    private function getExamsDatasource()
    {
        return Test::examFiltered(
            $this->filters,
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(self::PER_PAGE);

    }

    private function getPersonalDatasource()
    {
        $results = Test::filtered(
            $this->filters,
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
            $this->filters,
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

        $test->userDuplicate([], Auth::id());
    }

    public function openEdit($testUuid)
    {
        $this->redirect(route('teacher.question-editor', ['testId'=>$testUuid]));
    }




}
