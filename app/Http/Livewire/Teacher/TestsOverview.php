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
use tcCore\TemporaryLogin;

class TestsOverview extends Component
{
    use WithPagination;

    const PER_PAGE = 12;

    public $filters = [];

    private $sorting = ['id' => 'desc'];

    protected $queryString = ['openTab', 'referrerAction' => ['except' => '']];

    public $openTab = 'personal';

    public $referrerAction = '';

    public $selected = [];

    protected $listeners = [
        'test-deleted'        => '$refresh',
        'test-added'          => '$refresh',
        'testSettingsUpdated' => '$refresh',
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

    public function updatedFilters($value, $filter)
    {
        session(['tests-overview-filters' => $this->filters]);
    }

    public function updatingOpenTab($value)
    {
        $this->resetPage();
    }

    public function setOpenTab($tab)
    {
        if (in_array($tab, $this->allowedTabs)) {
            $this->openTab = $tab;
        }
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
        if (session()->has('tests-overview-filters'))
            $this->filters = session()->get('tests-overview-filters');
        else {
            collect($this->allowedTabs)->each(function ($tab) {
                $this->filters[$tab] = [
                    'name'                 => '',
                    'education_level_year' => [],
                    'education_level_id'   => [],
                    'subject_id'           => [],
                    'author_id'            => [],
                ];
            });
        }


        /** @TODO default search filter for teacher (is dirty now) */
//        $this->filters = array_merge($this->filters, auth()->user()->getSearchFilterDefaultsTeacher());
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

    public function openTestDetail($testUuid)
    {
        redirect()->to(route('teacher.test-detail', ['uuid' => $testUuid]));
    }

    public function openContextMenu($args)
    {
        $this->emitTo(
            'teacher.tests-overview-context-menu',
            'showMenu',
            $args
        );
    }

    public function clearFilters($tab = null)
    {
        $tabs = $tab ? [$tab] : $this->allowedTabs;
        collect($tabs)->each(function ($tab) {
            $this->filters[$tab] = [
                'name'                 => '',
                'education_level_year' => [],
                'education_level_id'   => [],
                'subject_id'           => [],
                'author_id'            => [],
            ];
        });
        session(['tests-overview-filters' => $this->filters]);
    }

    public function hasActiveFilters(): bool
    {
        return collect($this->filters[$this->openTab])
            ->when($this->openTab === 'personal', function ($collection) {
                return $collection->except('author_id');
            })
            ->whenEmpty(function ($collection) {
                return false;
            }, function ($collection) {
                return $collection->filter(function ($filter) {
                    return filled($filter);
                })->isNotEmpty();
            });
    }

    public function handleReferrerActions()
    {
        if (!$this->referrerAction) {
            return true;
        }

        if ($this->referrerAction === 'create_test') {
            $this->emit('openModal', 'teacher.test-create-modal');
            $this->referrerAction = '';
        }
    }

    public function toPlannedTest($takeUuid)
    {
        $url = sprintf("test_takes/view/%s", $takeUuid);
        $options = TemporaryLogin::buildValidOptionObject('page', $url);
        return auth()->user()->redirectToCakeWithTemporaryLogin($options);
    }
}
