<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\Subject;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\TemporaryLogin;
use tcCore\TestAuthor;
use tcCore\Traits\ContentSourceTabsTrait;

class TestsOverview extends Component
{
    use WithPagination, ContentSourceTabsTrait;

    const ACTIVE_TAB_SESSION_KEY = 'tests-overview-active-tab';
    const PER_PAGE = 12;

    private $sorting = ['id' => 'desc'];
    protected $queryString = [
        'openTab'        => ['as' => 'to_tab'],
        'referrerAction' => ['except' => '', 'as' => 'to_ra']
    ];

    public $filters = [];
    public $referrerAction = '';
    public $selected = [];
    public $mode;

    protected $listeners = [
        'test-deleted'        => '$refresh',
        'test-added'          => '$refresh',
        'testSettingsUpdated' => '$refresh',
    ];

    public function mount()
    {
        $this->isExamCoordinator = Auth::user()->isValidExamCoordinator();
        $this->abortIfNewTestBankNotAllowed();
        $this->initialiseContentSourceTabs();

        $this->setFilters();
    }

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

    protected function getDatasource()
    {
        try { // added for compatibility with mariadb
            \DB::select(\DB::raw("set session optimizer_switch='condition_fanout_filter=off';"));
        } catch (\Exception $e) {
        }

        switch ($this->openTab) {
            case 'school_location':
                $datasource = $this->getSchoolDatasource();
                break;
            case 'national':
                $datasource = $this->getNationalDatasource();
                break;
            case 'umbrella':
                $datasource = $this->getUmbrellaDatasource();
                break;
            case 'creathlon':
                $datasource = $this->getCreathlonDatasource();
                break;
            case 'personal':
            default :
                $datasource = $this->getPersonalDatasource();
                break;

        }
        return $datasource
            ->with([
                'educationLevel',
                'testKind',
                'subject',
                'author',
                'author.school',
                'author.schoolLocation',
                'testAuthors:test_id,user_id',
                'testAuthors.user:id,name,name_first,name_suffix',
            ])
            ->paginate(self::PER_PAGE);
    }

    private function getSchoolDatasource()
    {
        return Test::filtered(
            array_merge(
                $this->cleanFilterForSearch($this->filters['school_location']),
                ['owner_id' => auth()->user()->school_location_id]
            ),
            $this->sorting
        );
    }


    private function getNationalDatasource()
    {
        return Test::nationalItemBankFiltered(
            $this->getContentSourceFilters('national'),
            $this->sorting
        );
    }

    private function getPersonalDatasource()
    {
        $this->filters['personal']['author_id'] = auth()->id();

        return Test::filtered(
            $this->cleanFilterForSearch($this->filters['personal']),
            $this->sorting
        )
            ->where('tests.author_id', auth()->id());
    }

    private function getUmbrellaDatasource()
    {
        return Test::sharedSectionsFiltered(
            $this->cleanFilterForSearch($this->filters['umbrella']),
            $this->sorting
        );
    }

    private function getCreathlonDatasource()
    {
        return Test::creathlonItemBankFiltered(
            $this->getContentSourceFilters('creathlon'),
            $this->sorting
        );
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
        if ($this->isExternalContentTab($this->openTab)) {
            return $this->getBaseSubjectsOptions();
        }
        return [];
    }

    private function getBaseSubjectsOptions()
    {
        if (Auth::user()->isValidExamCoordinator()) {
            return BaseSubject::optionList();
        }

        return BaseSubject::whereIn('id', Subject::filtered(['user_current' => Auth::id()], [])->pluck('base_subject_id'))
            ->optionList();
    }

    public function getSubjectsProperty()
    {
        return $this->filterSubjectsByTabName($this->openTab)
            ->optionList();
    }

    private function filterSubjectsByTabName(string $tab)
    {
        return Subject::filtered(['imp' => 0, 'user_id' => Auth::id()], ['name' => 'asc']);
    }

    public function getEducationLevelYearProperty()
    {
        return collect(range(1, 6))->map(function ($item) {
            return ['value' => (int)$item, 'label' => (string)$item];
        })->toArray();
    }

    public function getAuthorsProperty()
    {
        return TestAuthor::when($this->openTab === 'umbrella', function ($query) {
            return $query->schoolLocationAndSharedSectionsAuthorUsers(Auth::user());
        }, function ($query) {
            return $query->schoolLocationAuthorUsers(Auth::user());
        })
            ->get()
            ->when($this->openTab === 'umbrella', function ($users) {
                return $users->reject(function ($user) {
                    return ($user->school_location_id === Auth::user()->school_location_id && $user->getKey() !== Auth::id());
                });
            })
            ->map(function ($author) {
                return ['value' => $author->id, 'label' => trim($author->name_first . ' ' . $author->name)];
            })->values()->toArray();
    }

    private function cleanFilterForSearch(array $filters)
    {
        return collect($filters)->reject(function ($filter) {
            return $filter instanceof Collection ? $filter->isEmpty() : empty($filter);
        })->toArray();
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
            $this->dispatchBrowserEvent('notify', ['message' => __('teacher.Test is verwijderd')]);
            $this->referrerAction = '';
        }
    }

    public function canFilterOnAuthors(): bool
    {
        return collect($this->canFilterOnAuthorTabs)->contains($this->openTab);
    }

    private function tabNeedsDefaultFilters($tab): bool
    {
        return collect($this->schoolLocationInternalContentTabs)->contains($tab) && !Auth::user()->isValidExamCoordinator();
    }

    public function getMessageKey($resultsCount): string
    {
        if ($resultsCount > 0 || $this->hasActiveFilters()) {
            return 'general.number-of-tests';
        }

        return 'general.number-of-tests-' . $this->openTab;
    }

    /**
     * @return void
     */
    public function abortIfNewTestBankNotAllowed(): void
    {
        if (auth()->user()->schoolLocation->allow_new_test_bank !== 1) {
            abort(403);
        }
    }

    public function toPlannedTest($takeUuid)
    {
        $testTake = TestTake::whereUuid($takeUuid)->first();
        if ($testTake->isAssessmentType()) {
            $url = sprintf("test_takes/assessment_open_teacher/%s", $takeUuid);
        } else {
            $url = sprintf("test_takes/view/%s", $takeUuid);
        }
        $options = TemporaryLogin::buildValidOptionObject('page', $url);
        return auth()->user()->redirectToCakeWithTemporaryLogin($options);
    }

    /**
     * @return array
     */
    private function getContentSourceFilters($tab): array
    {
        $filters = $this->cleanFilterForSearch($this->filters[$tab]);
        if (!isset($filters['base_subject_id']) && !Auth::user()->isValidExamCoordinator()) {
            $filters['base_subject_id'] = BaseSubject::currentForAuthUser()->pluck('id')->toArray();
        }
        return $filters;
    }
}
