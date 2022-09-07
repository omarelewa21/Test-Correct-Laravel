<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\GroupQuestion;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Requests\CreateGroupQuestionQuestionRequest;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Question;
use tcCore\Subject;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\Traits\ContentSourceTabsTrait;

class QuestionBank extends Component
{
    use ContentSourceTabsTrait;
    const ACTIVE_TAB_SESSION_KEY = 'question-bank-active-tab';

    const ITEM_INCREMENT = 16;

    const SOURCE_PERSONAL = 'me';
    const SOURCE_SCHOOL = '';

    protected $queryString = ['testId', 'testQuestionId'];

    public $testId;
    public $testQuestionId;

    public $filters = [];

    public $addedQuestionIds = [];
    public $itemsPerPage;

    public $inGroup = false;

    private $test;

    public $groupQuestionDetail;

    protected function getListeners()
    {
        return [
            'testSettingsUpdated',
            'addQuestionFromDetail' => 'handleCheckboxClick',
            'questionDeleted'       => 'questionDeletedFromExternalComponent',
            'newGroupId'            => 'newGroupId',
        ];
    }

    public function mount()
    {
        $this->initialiseContentSourceTabs();

        $this->itemsPerPage = QuestionBank::ITEM_INCREMENT;
        $this->setTestProperty();
        $this->setAddedQuestionIdsArray();
        $this->setFilters();
    }

    public function render()
    {
        return view('livewire.teacher.question-bank');
    }

    private function getFilters()
    {
        return collect($this->filters[$this->openTab])->reject(function ($filter) {
            return empty($filter);
        })
            ->when($this->openTab === 'personal', function ($filters) {
                return $filters->merge(['source' => 'me']);
            })
            ->when($this->openTab === 'school_location', function ($filters) {
                return $filters->merge(['source' => 'schoolLocation']);
            })
            ->when($this->openTab === 'national', function ($filters) {
                return $filters->merge(['source' => 'national']);
            })
            ->when($this->openTab === 'creathlon', function ($filters) {
                return $filters->merge(['source' => 'creathlon']);
            })
            ->when((isset($this->filters[$this->openTab]['search']) && is_numeric($this->filters[$this->openTab]['search'])), function ($filters) {
                unset($filters['search']);
                return $filters->merge(['id' => $this->filters[$this->openTab]['search']]);
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
        return EducationLevel::filtered(['user_id' => Auth::id()])->optionList();
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
        $this->setTestProperty();
    }

    private function setTestProperty()
    {
        $this->test = Test::whereUuid($this->testId)->first();
    }

    public function handleCheckboxClick($questionUuid)
    {
        $question = Question::whereUuid($questionUuid)->firstOrFail();
        if ($question->needsCleanCopy()) {
            $question = $this->createCleanCopyToAddToTest($question);
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
        }
        return true;
    }

    private function getQuestionIdsThatAreAlreadyInTest()
    {
        $questionIdList = optional($this->test)->getQuestionOrderList() ?? [];
        if (!$this->test) {
            return $questionIdList;
        }

        return $questionIdList + $this->test->testQuestions->map(function ($testQ) {
                return $testQ->question()->where('type', 'GroupQuestion')->value('id');
            })->filter()->flip()->toArray();
    }

    private function removeQuestionFromTest($questionId)
    {
        $this->addedQuestionIds = collect($this->addedQuestionIds)->reject(function ($index, $id) use ($questionId) {
            return $id == $questionId;
        });
    }

    public function isQuestionInTest($questionId)
    {
        return isset($this->addedQuestionIds[$questionId]);
    }

    public function showMore()
    {
        $this->itemsPerPage += QuestionBank::ITEM_INCREMENT;
    }

    public function updatedFilters($name, $value)
    {
        $this->resetItemsPerPage();
    }

    public function updatedInGroup($value)
    {
        collect($this->allowedTabs)->each(function ($tab) use ($value) {
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
            "owner_id"          => $this->inGroup
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
        ];

        return (new TestQuestionsController)->store(new CreateTestQuestionRequest($requestParams));
    }

    private function setFilters()
    {
        collect($this->allowedTabs)->each(function ($tab) {
            $this->filters[$tab] = $this->subjectFilterForTab($tab) + [
                    'search'               => '',
                    'education_level_year' => [$this->test->education_level_year],
                    'education_level_id'   => [$this->test->education_level_id],
                    'without_groups'       => '',
                    'author_id'            => []
                ];
        });
    }

    public function clearFilters($tab = null)
    {
        $tabs = $tab ? [$tab] : $this->allowedTabs;
        return collect($tabs)->each(function ($tab) {
            $this->filters[$tab] = [
                'search'               => '',
                'education_level_year' => [],
                'education_level_id'   => [],
                'subject_id'           => [],
                'base_subject_id'      => [],
                'author_id'            => [],
            ];
        });
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
//        $this->groupQuestionDetail->loadRelated();
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
        return collect($this->filters[$this->openTab])->filter(function ($filter) {
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

    public function setAddedQuestionIdsArray(): void
    {
        $this->addedQuestionIds = $this->getQuestionIdsThatAreAlreadyInTest();
    }

    private function subjectFilterForTab($tab): array
    {
        if ($this->isExternalContentTab($tab)) {
            return ['base_subject_id' => $this->test->subject()->pluck('base_subject_id')];
        }
        return ['subject_id' => [$this->test->subject_id]];
    }

    public function hasAuthorFilter($tab = null): bool
    {
        return collect(['school_location'])->contains($tab ?? $this->openTab);
    }

    private function questionDataSource()
    {
        if ($this->isExternalContentTab()) {
            return Question::publishedFiltered($this->getFilters());
        }
        return Question::filtered($this->getFilters());
    }

    private function createCleanCopyToAddToTest(Question $question)
    {
        $newQuestion = $question->duplicate($question->getAttributes());
        Question::whereId($newQuestion->getKey())->update([
            'scope'                    => null,
            'derived_question_id'      => null,
            'education_level_id'       => $this->test->education_level_id,
            'education_level_year'     => $this->test->education_level_year,
            'subject_id'               => $this->test->subject_id,
            'add_to_database'          => false,
            'add_to_database_disabled' => true,
        ]);

        return $newQuestion;
    }
}