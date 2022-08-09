<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Subject;
use tcCore\Test;

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
        'personal', /*Persoonlijk*/
        'school', /*School / Schoollocatie*/
        'umbrella', /*Scholengemeenschap*/
        'national', /*Nationaal*/
    ];
    private $defaultFilterTabs = [
        'personal',
        'school',
    ];
    private $publicTestsTabs = ['umbrella', 'national'];

    public bool $hasSharedSections;

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
            case 'national':
                $datasource = $this->getNationalDatasource();
                break;
            case 'umbrella':
                $datasource = $this->getUmbrellaDatasource();
                break;
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


    private function getNationalDatasource()
    {
        return Test::nationalItemBankFiltered(
            $this->cleanFilterForSearch($this->filters['national']),
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

    private function getUmbrellaDatasource()
    {
        return Test::sharedSectionsFiltered(
            $this->cleanFilterForSearch($this->filters['umbrella']),
            $this->sorting
        )
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(self::PER_PAGE);
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
                    'base_subject_id'      => [],
                ];
                if ($this->tabNeedsDefaultFilters($tab)) {
                    $this->filters[$tab] = array_merge($this->filters[$tab], auth()->user()->getSearchFilterDefaultsTeacher());
                }
            });
        }
    }


    public function getEducationLevelProperty()
    {
        return EducationLevel::filtered(['user_id' => auth()->id()], ['name' => 'desc'])
            ->get(['id', 'name'])
            ->map(function ($educationLevel) {
                return ['value' => (int)$educationLevel->id, 'label' => $educationLevel->name];
            });
    }

    public function getBasesubjectsProperty()
    {
        if ($this->isPublicTestTab($this->openTab)) {
            return $this->getBaseSubjectsOptions();
        }
        return [];
    }

    private function getBaseSubjectsOptions()
    {
        return BaseSubject::whereIn('id', Subject::filtered(['user_current' => Auth::id()], [])->pluck('base_subject_id'))
            ->get(['name', 'id'])
            ->map(function ($subject) {
                return ['value' => (int)$subject->id, 'label' => $subject->name];
            })->toArray();
    }

    public function getSubjectsProperty()
    {
        return $this->filterSubjectsByTabName($this->openTab)
            ->get(['name', 'id'])
            ->map(function ($subject) {
                return ['value' => (int)$subject->id, 'label' => $subject->name];
            })->toArray();
    }

    private function filterSubjectsByTabName(string $tab)
    {
        switch ($tab) {
            case 'cito':
                return Subject::citoFiltered([], ['name' => 'asc']);
            case 'national':
                return Subject::nationalItemBankFiltered([], ['name' => 'asc']);
            case 'exams':
                return Subject::examFiltered([], ['name' => 'asc']);
            default:
                return Subject::filtered(['imp' => 0, 'user_id' => Auth::id()], ['name' => 'asc']);
        }
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
        if (!collect($this->allowedTabs)->contains($this->openTab)) {
            abort(404);
        }
        $this->setFilters();
        $this->hasSharedSections = Auth::user()->hasSharedSections();
    }

    private function cleanFilterForSearch(array $filters)
    {
        $searchFilter = [];
        foreach (['name', 'education_level_year', 'education_level_id', 'subject_id', 'author_id', 'base_subject_id'] as $filter) {
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
                'base_subject_id'      => []
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
        if ($this->referrerAction === 'test_deleted') {
            $this->dispatchBrowserEvent('notify', ['message'=> __('teacher.Test is verwijderd')]);
            $this->referrerAction = '';
        }
    }

    public function canFilterOnAuthors()
    {
        return !collect(['personal', 'national'])->contains($this->openTab);
    }

    private function tabNeedsDefaultFilters($tab)
    {
        return collect($this->defaultFilterTabs)->contains($tab);
    }

    public function isPublicTestTab($tab)
    {
        return collect($this->publicTestsTabs)->contains($tab);
    }
}
