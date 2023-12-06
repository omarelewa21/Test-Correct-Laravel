<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use tcCore\BaseSubject;
use tcCore\GroupQuestion;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Requests\CreateGroupQuestionQuestionRequest;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\Http\Traits\WithAddExistingQuestionFilterSync;
use tcCore\Http\Traits\WithQueryStringSyncing;
use tcCore\Http\Traits\WithTestAwarenessProperties;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Lib\Repositories\TaxonomyRepository;
use tcCore\Question;
use tcCore\Subject;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\Traits\ContentSourceTabsTrait;
use tcCore\UserSystemSetting;

class QuestionBank extends TCComponent
{
    use ContentSourceTabsTrait, WithQueryStringSyncing, WithTestAwarenessProperties, WithAddExistingQuestionFilterSync;

    const ACTIVE_TAB_SESSION_KEY = 'question-bank-active-tab';

    const ITEM_INCREMENT = 16;

    protected $queryString = ['testId', 'testQuestionId', 'openTab' => ['as' => 'qb_ot']];
    protected $test;

    public $testId;
    public $testQuestionId;
    public $filters = [];
    public $itemsPerPage;
    public $inGroup = false;
    public $active;
    public $groupQuestionDetail;
    public $inTestBankContext = false;
    public $showQuestionBank = true;

    protected string $filterIdentifyingAttribute = 'testId';
    protected array $filterableAttributes = [
        'search'               => '',
        'education_level_year' => [],
        'education_level_id'   => [],
        'subject_id'           => [],
        'author_id'            => [],
        'base_subject_id'      => [],
        'taxonomy'             => [],
    ];

    protected function getListeners(): array
    {
        return [
            'testSettingsUpdated',
            'addQuestionFromDetail' => 'handleCheckboxClick',
            'questionDeleted'       => 'questionDeletedFromExternalComponent',
            'newGroupId'            => 'newGroupId',
            'shared-filter-updated' => 'loadSharedFilters',
        ];
    }

    public function mount()
    {
        $this->initialiseContentSourceTabs();

        $this->itemsPerPage = QuestionBank::ITEM_INCREMENT;
        if(!$this->inTestBankContext){
            $this->setTestProperty();
            $this->setAddedQuestionIdsArray();
        }

        $this->setFilters();
    }

    public function render()
    {
        return view('livewire.teacher.question-bank');
    }

    private function getFilters(): array
    {
        $needsIdSearch = (isset($this->filters['search']) && is_numeric($this->filters['search']));
        $filters = collect($this->filters)->diffKeys(array_flip($this->getNotAllowedFilterProperties()));

        return $filters->reject(function ($filter) {
            return empty($filter);
        })
            ->when($this->openTab === 'personal', fn($filters) => $filters->merge(['source' => 'me']))
            ->when($this->openTab === 'school_location', fn($filters) => $filters->merge(['source' => 'schoolLocation', 'draft' => false]))
            ->when($this->openTab === 'umbrella', fn($filters) => $filters->merge(['source' => 'school', 'draft' => false]))
            ->when($this->openTab === 'national', fn($filters) => $filters->merge(['source' => 'national']))
            ->when($this->openTab === 'creathlon', fn($filters) => $filters->merge(['source' => 'creathlon']))
            ->when($this->openTab === 'olympiade', fn($filters) => $filters->merge(['source' => 'olympiade']))
            ->when($this->openTab === 'olympiade_archive', fn($filters) => $filters->merge(['source' => 'olympiade_archive']))
            ->when($this->openTab === 'thieme_meulenhoff', fn($filters) => $filters->merge(['source' => 'thieme_meulenhoff']))
            ->when($this->openTab === 'formidable', fn($filters) => $filters->merge(['source' => 'formidable']))
            ->when($needsIdSearch, function ($filters) {
                unset($filters['search']);
                return $filters->merge(['id' => $this->filters['search']]);
            })
            ->toArray();
    }

    public function getSubjectsProperty()
    {
        return Subject::filtered(['user_id' => Auth::id()], ['name' => 'asc'])->optionList();
    }

    public function getBaseSubjectsProperty()
    {
        return BaseSubject::currentForAuthUser()->optionList();
    }

    public function getEducationLevelProperty()
    {
        return $this->filterableEducationLevelsBasedOnTab();
    }

    public function getEducationLevelYearProperty()
    {
        return [
            ['value' => 1, 'label' => '1'],
            ['value' => 2, 'label' => '2'],
            ['value' => 3, 'label' => '3'],
            ['value' => 4, 'label' => '4'],
            ['value' => 5, 'label' => '5'],
            ['value' => 6, 'label' => '6'],
        ];
    }

    public function getAuthorsProperty()
    {
        return (new AuthorsController())->getBuilderWithAuthors()
            ->map(function ($author) {
                return ['value' => $author->id, 'label' => trim($author->name_first . ' ' . $author->name)];
            })->toArray();
    }

    public function booted()
    {
        if(!$this->inTestBankContext) $this->setTestProperty();
    }

    private function setTestProperty()
    {
        $this->test = Test::whereUuid($this->testId)->first();
    }

    public function handleCheckboxClick($questionUuid)
    {
        $question = Question::whereUuid($questionUuid)->firstOrFail();
        if ($question->needsCleanCopy()) {
            $question = $question->createCleanCopy(
                $this->test->education_level_id,
                $this->test->education_level_year,
                $this->test->subject_id,
                $this->test->draft,
                auth()->user()
            );
        }

        return $this->addQuestionToTest($question->getKey());
    }

    public function addQuestionToTest($questionId)
    {
        if ($this->inGroup) {
            $response = $this->peformControllerActionForSubQuestion($questionId);
        } else {
            $response = $this->performControllerActionForQuestion($questionId);
        }

        if (empty($this->addedQuestionIds)) {
            $this->dispatchBrowserEvent('first-question-of-test-added');
        }

        if ($response->getStatusCode() == 200) {
            $this->addedQuestionIds[json_decode($response->getContent())->question_id] = 0;
            $this->dispatchBrowserEvent('question-added');
            $this->emit('updateQuestionsInTest');
        }
        return true;
    }

    public function showMore()
    {
        $this->itemsPerPage += QuestionBank::ITEM_INCREMENT;
    }

    public function updatedFilters($name, $value)
    {
        $this->resetItemsPerPage();
        UserSystemSetting::setSetting(auth()->user(), $this->getFilterSessionKey(), $this->filters);
    }

    public function updatedInGroup($value)
    {
        collect($this->allowedTabs)->each(function ($item, $tab) use ($value) {
            $this->filters[$tab]['without_groups'] = $value;
        });
    }

    private function resetItemsPerPage()
    {
        $this->itemsPerPage = QuestionBank::ITEM_INCREMENT;
    }

    private function getQuestionsQuery()
    {
        return $this->questionDataSource()
            ->when(!$this->inGroup, function ($query) {
                $query->where('is_subquestion', 0);
            }, function ($query) {
                $query->where('type', '!=', 'GroupQuestion');
            })
            ->orderby('created_at', 'desc')
            ->distinct();
    }

    public function getQuestionsProperty()
    {
        return $this->getQuestionsQuery()
            ->take($this->itemsPerPage)
            ->withCount('attachments')
            ->with(['subject:id,name', 'authors:id,name,name_first,name_suffix'])
            ->get(['questions.*']);
    }

    public function getResultCountProperty()
    {
        return $this->getQuestionsQuery()->count();
    }

    private function peformControllerActionForSubQuestion($questionId)
    {
        $requestParams = [
            "group_question_id" => $this->inGroup,
            "order"             => 0,
            "maintain_position" => 0,
            "discuss"           => 1,
            "closeable"         => 0,
            "question_id"       => $questionId,
            "owner_id"          => $this->inGroup,
            'draft'             => $this->test->draft,
        ];

        $gqqm = GroupQuestionQuestionManager::getInstanceWithUuid($this->inGroup);
        $cgqqr = new CreateGroupQuestionQuestionRequest($requestParams);

        return (new GroupQuestionQuestionsController)->store($gqqm, $cgqqr);
    }

    private function performControllerActionForQuestion($questionId)
    {
        $requestParams = [
            'test_id'           => $this->test->getKey(),
            'order'             => 0,
            'maintain_position' => 0,
            'discuss'           => 1,
            'closeable'         => 0,
            'question_id'       => $questionId,
            'draft'             => $this->test->draft,
        ];

        return (new TestQuestionsController)->store(new CreateTestQuestionRequest($requestParams));
    }

    private function setFilters(array $filters = null)
    {
        if ($filters) {
            $this->filters = $filters;
            return;
        }

        $storedFilters = UserSystemSetting::getSetting(
            user: auth()->user(),
            title: $this->getFilterSessionKey(),
            sessionStore: true
        );
        if ($storedFilters) {
            $this->filters = array_merge($this->filterableAttributes, $storedFilters);
            return;
        }

        if($this->inTestBankContext) {
            $this->filters = $this->filterableAttributes;
            return;
        }
        $this->filters = $this->defaultFilters();
    }

    private function defaultFilters(): array
    {
        return array_merge($this->filterableAttributes, [
//            'education_level_year' => [$this->test->education_level_year],
//            'education_level_id'   => [$this->test->education_level_id],
            'subject_id'           => [$this->test->subject_id],
            'base_subject_id'      => $this->test->subject()->pluck('base_subject_id')
        ]);
    }

    public function clearFilters()
    {
        $this->filters = $this->filterableAttributes;
        UserSystemSetting::setSetting(auth()->user(), $this->getFilterSessionKey(), $this->filters);
        $this->notifySharedFilterComponents();
    }

    public function openDetail($questionUuid, $inTest)
    {
        $this->emit(
            'openModal',
            'teacher.question-detail-modal',
            [
                'questionUuid' => $questionUuid,
                'inTest'       => $inTest
            ]
        );
    }

    public function showGroupDetails($groupUuid, $inTest = false)
    {
        $groupQuestionId = Question::whereUuid($groupUuid)->value('id');
        $this->groupQuestionDetail = GroupQuestion::whereId($groupQuestionId)
            ->with(['groupQuestionQuestions', 'groupQuestionQuestions.question'])
            ->first();

        $this->groupQuestionDetail->inTest = $inTest;

        return true;
    }

    public function clearGroupDetails()
    {
        $this->reset('groupQuestionDetail');
    }

    public function testSettingsUpdated($newData)
    {
        /* @TODO
         * Fix the resetting of filters when the test is edited from TestEditModal;
         */
    }

    public function hasActiveFilters()
    {
        return collect($this->filters)->filter(function ($filter) {
            return filled($filter);
        })->isNotEmpty();
    }

    public function questionDeletedFromExternalComponent($testQuestionUuid)
    {
        $questionId = TestQuestion::whereUuid($testQuestionUuid)->withTrashed()->value('question_id');
        $this->removeQuestionFromTest($questionId);
    }

    public function openPreview($questionUuid, $inTest)
    {
        $this->emit('openModal', 'teacher.question-cms-preview-modal', ['uuid' => $questionUuid, 'inTest' => $inTest]);
    }

    public function newGroupId($uuid)
    {
        $this->inGroup = $uuid;
        $this->updatedInGroup($uuid);
    }

    public function hasAuthorFilter($tab = null): bool
    {
        return collect(['school_location'])->contains($tab ?? $this->openTab);
    }

    private function questionDataSource()
    {
        if ($this->isExternalContentTab() && $this->openTab !== 'umbrella') {
            return Question::publishedFiltered($this->getFilters())->published();
        }
        return Question::filtered($this->getFilters());
    }

    public function clearInGroupProperty(): bool
    {
        $this->skipRender();
        return $this->inGroup = false;
    }

    /**
     * @return array|string[]
     */
    private function getNotAllowedFilterProperties(): array
    {
        $source = $this->getSourceForFilterNotAllowed($this->openTab);
        $notAllowed = [
            'personal'        => ['base_subject_id', 'author_id'],
            'school_location' => ['base_subject_id'],
            'external'        => ['subject_id', 'author_id'],
        ];

        return $notAllowed[$source];
    }

    private function getSourceForFilterNotAllowed($tab): string
    {
        if ($tab === 'personal') return $tab;
        if ($tab === 'school_location') return $tab;
        return 'external';
    }

    public function getTaxonomiesProperty()
    {
        return TaxonomyRepository::choicesOptions();
    }

    public function updatingShowQuestionBank($value)
    {
        if(!$value) $this->emitUp('showTestBank');
    }
}