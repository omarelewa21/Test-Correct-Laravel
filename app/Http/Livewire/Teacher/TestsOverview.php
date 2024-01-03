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
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\NationalItemBankService;
use tcCore\Services\ContentSource\OlympiadeService;
use tcCore\Services\ContentSource\PersonalService;
use tcCore\Services\ContentSource\SchoolLocationService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\Services\ContentSource\UmbrellaOrganizationService;
use tcCore\Services\ContentSourceFactory;
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
        if ($this->showQuestionBank)
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
        return ContentSourceFactory::makeWithTab($this->openTab)->itemBankFiltered(
            auth()->user(),
            $this->getContentSourceFilters(),
            $this->sorting,
        )
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

    protected function setFilters(array $filters = null): void
    {
        parent::setFilters($filters);

        if (!UserSystemSetting::hasSetting(auth()->user(), $this->getFilterSessionKey())) {
            $this->mergeFiltersWithDefaults();
        }
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

    public function openTestDetail($testUuid)
    {
        redirect()->to(route('teacher.test-detail', ['uuid' => $testUuid]));
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


    protected function mergeFiltersWithDefaults(): void
    {
        $this->filters = array_merge($this->filters, auth()->user()->getSearchFilterDefaultsTeacher());
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
