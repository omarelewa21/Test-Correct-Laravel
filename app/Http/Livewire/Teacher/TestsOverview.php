<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\Http\Livewire\OverviewComponent;
use tcCore\Http\Helpers\Choices\Choice;
use tcCore\Lib\Repositories\TaxonomyRepository;
use tcCore\Subject;
use tcCore\TemporaryLogin;
use tcCore\Test;
use tcCore\TestAuthor;
use tcCore\TestTake;
use tcCore\Traits\ContentSourceTabsTrait;
use tcCore\UserSystemSetting;

class TestsOverview extends OverviewComponent
{
    use ContentSourceTabsTrait;

    const ACTIVE_TAB_SESSION_KEY = 'tests-overview-active-tab';
    protected string $sessionKey = 'tests-overview';

    private $sorting = ['id' => 'desc'];
    protected $queryString = [
        'openTab'        => ['as' => 'to_tab'],
        'referrerAction' => ['except' => ''],
        'file'           => ['except' => ''],
    ];

    public $referrerAction = '';
    public $file = '';
    public $selected = [];
    public $mode;
    public $inTestBankContext = true;
    public $showQuestionBank = false;
    protected array $filterableAttributes = [
        'name'                      => '',
        'education_level_year'      => [],
        'education_level_id'        => [],
        'subject_id'                => [],
        'author_id'                 => [],
        'shared_sections_author_id' => [],
        'base_subject_id'           => [],
        'taxonomy'                  => [],
    ];

    protected $listeners = [
        'test-deleted'        => '$refresh',
        'test-added'          => '$refresh',
        'testSettingsUpdated' => '$refresh',
        'test-updated'        => '$refresh',
        'showTestBank',
    ];

    public function mount()
    {
        $this->isExamCoordinator = Auth::user()->isValidExamCoordinator();
        $this->abortIfNewTestBankNotAllowed();
        $this->initialiseContentSourceTabs();

        parent::mount();
    }

    public function render()
    {
        if($this->showQuestionBank)
            return view('livewire.teacher.question-bank-overview')->layout('layouts.app-teacher');

        $results = $this->getDatasource();
        return view('livewire.teacher.tests-overview')->layout('layouts.app-teacher')->with(compact(['results']));
    }

    protected function getDatasource()
    {
        try { // added for compatibility with mariadb
            $expression = DB::raw("set session optimizer_switch='condition_fanout_filter=off';");
            DB::statement($expression->getValue(DB::connection()->getQueryGrammar()));
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
            case 'formidable':
                $datasource = $this->getFormidableDatasource();
                break;
            case 'creathlon':
                $datasource = $this->getCreathlonDatasource();
                break;
            case 'olympiade':
                $datasource = $this->getOlympiadeDatasource();
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
        return Test::schoolFiltered(
            array_merge(
                $this->cleanFilterForSearch($this->filters, 'school_location'),
                ['owner_id' => auth()->user()->school_location_id]
            ),
            $this->sorting
        );
    }

    private function getNationalDatasource()
    {
        return Test::nationalItemBankFiltered(
            $this->getContentSourceFilters(),
            $this->sorting
        );
    }

    private function getPersonalDatasource()
    {
        $filters = $this->filters;
        $filters['author_id'] = [auth()->id()];

        return Test::filtered(
            $this->cleanFilterForSearch($filters, 'personal'),
            $this->sorting
        )
            ->where('tests.author_id', auth()->id());
    }

    private function getUmbrellaDatasource()
    {
        return Test::sharedSectionsFiltered(
            $this->getUmbrellaDatasourceFilters(),
            $this->sorting
        );
    }

    private function getCreathlonDatasource()
    {
        return Test::creathlonItemBankFiltered(
            $this->getContentSourceFilters(),
            $this->sorting
        );
    }

    private function getFormidableDatasource()
    {
        return Test::formidableItemBankFiltered(
            $this->getContentSourceFilters(),
            $this->sorting
        );
    }


    private function getOlympiadeDatasource()
    {
        return Test::olympiadeItemBankFiltered(
            $this->getContentSourceFilters(),
            $this->sorting
        );
    }

    protected function setFilters(array $filters = null): void
    {
        parent::setFilters($filters);

        if (!UserSystemSetting::hasSetting(auth()->user(), $this->getFilterSessionKey())) {
            $this->mergeFiltersWithDefaults();
        }
    }


    public function getEducationLevelProperty()
    {
        return $this->filterableEducationLevelsBasedOnTab();
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

        return BaseSubject::whereIn('id', Subject::filtered(['user_current' => Auth::id()], [])->select('base_subject_id'))
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

    public function getSharedSectionsAuthorsProperty()
    {
        return TestAuthor::schoolLocationAndSharedSectionsAuthorUsers(Auth::user())
            ->get()
            ->reject(function ($user) {
                return ($user->school_location_id === Auth::user()->school_location_id && $user->getKey() !== Auth::id());
            })
            ->map(function ($author) {
                return ['value' => $author->id, 'label' => trim($author->name_first . ' ' . $author->name)];
            })
            ->values()
            ->toArray();
    }

    public function getAuthorsProperty()
    {
        return TestAuthor::schoolLocationAuthorUsers(Auth::user())
            ->get()
            ->map(function ($author) {
                return ['value' => $author->id, 'label' => trim($author->name_first . ' ' . $author->name)];
            })
            ->values()
            ->toArray();
    }

    private function cleanFilterForSearch(array $filters, string $source): array
    {
        $notAllowed = $this->getNotAllowedFilterProperties($source);

        return collect($filters)->reject(function ($filter, $key) use ($notAllowed) {
            if ($filter instanceof Collection) {
                return $filter->isEmpty() || in_array($key, $notAllowed);
            } else {
                return empty($filter) || in_array($key, $notAllowed);
            }
        })->toArray();
    }

    public function openTestDetail($testUuid)
    {
        redirect()->to(route('teacher.test-detail', ['uuid' => $testUuid]));
    }

    public function clearFilters(): void
    {
        parent::clearFilters();

        UserSystemSetting::setSetting(auth()->user(), $this->getFilterSessionKey(), $this->filters);
    }

    public function hasActiveFilters(): bool
    {
        return collect($this->filters)
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
            $params = ['teacher.test-create-modal'];
            if (Uuid::isValid($this->file)) {
                $params = [
                    'toetsenbakker.test-create-modal',
                    ['fileManagement' => $this->file]
                ];
            }

            $this->emit('openModal', ...$params);
            $this->reset('referrerAction', 'file');
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

    protected function tabNeedsDefaultFilters($tab): bool
    {
        return collect($this->schoolLocationInternalContentTabs)->has($tab) && !Auth::user()->isValidExamCoordinator();
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
        return auth()->user()->redirectToCakeWithTemporaryLogin($testTake->getPlannedTestOptions());
    }

    private function getContentSourceFilters(): array
    {
        $filters = $this->cleanFilterForSearch($this->filters, 'external');
        if (!isset($filters['base_subject_id']) && !Auth::user()->isValidExamCoordinator()) {
            $filters['base_subject_id'] = BaseSubject::currentForAuthUser()->pluck('id')->toArray();
        }
        return $filters;
    }

    protected function mergeFiltersWithDefaults(): void
    {
        $this->filters = array_merge($this->filters, auth()->user()->getSearchFilterDefaultsTeacher());
    }

    /**
     * @param string $source
     * @return array|string[]
     */
    private function getNotAllowedFilterProperties(string $source): array
    {
        $notAllowed = [
            'personal'        => ['base_subject_id', 'author_id', 'shared_sections_author_id'],
            'school_location' => ['base_subject_id', 'shared_sections_author_id'],
            'umbrella'        => ['subject_id', 'author_id'],
            'external'        => ['subject_id', 'author_id', 'shared_sections_author_id'],
        ];

        return $notAllowed[$source];
    }

    /**
     * @return array
     */
    private function getUmbrellaDatasourceFilters(): array
    {
        $filters = $this->cleanFilterForSearch($this->filters, 'umbrella');
        if (!empty($filters['shared_sections_author_id'])) {
            $filters['author_id'] = $filters['shared_sections_author_id'];
        }
        return $filters;
    }

    public function getTaxonomiesProperty()
    {
        return TaxonomyRepository::choicesOptions();
    }

    public function showTestBank()
    {
        $this->showQuestionBank = false;
        $this->render();
    }
}
