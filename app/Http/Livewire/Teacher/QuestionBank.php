<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Requests\CreateGroupQuestionQuestionRequest;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Question;
use tcCore\Subject;
use tcCore\Test;

class QuestionBank extends Component
{
    const ITEM_INCREMENT = 16;

    const SOURCE_PERSONAL = 'me';
    const SOURCE_SCHOOL = '';

    protected $queryString = ['testId', 'testQuestionId'];

    public $openTab = 'personal';
    public $testId;
    public $testQuestionId;

    public $filters = [];

    public $addedQuestionIds = [];
    public $itemsPerPage;

    public $inGroup = false;

    private $allowedTabs = [
        'school_location',
        'personal',
    ];

    public function mount()
    {
        $this->itemsPerPage = QuestionBank::ITEM_INCREMENT;
        $this->addedQuestionIds = $this->getQuestionIdsThatAreAlreadyInTest();
        $this->setFilters();
    }

    public function render()
    {
        return view('livewire.teacher.question-bank');
    }

    public function getQuestionsProperty()
    {
        return $this->getQuestionsQuery()
            ->take($this->itemsPerPage)
            ->get(['questions.*']);
    }

    private function getFilters()
    {
        return collect($this->filters[$this->openTab])->reject(function ($filter) {
            return empty($filter);
        })
            ->when($this->openTab === 'personal', function ($filters) {
                return $filters->merge(['source' => 'me']);
            })
            ->toArray();
    }

    public function getSubjectsProperty()
    {
        return Subject::filtered(['user_id' => Auth::id()], ['name' => 'asc'])
            ->with('baseSubject')
            ->get()
            ->map(function ($subject) {
                return [
                    'value' => $subject->getKey(),
                    'label' => $subject->name
                ];
            })
            ->toArray();
    }

    public function getEducationLevelProperty()
    {
        return EducationLevel::filtered(['user_id' => Auth::id()])
            ->get()
            ->map(function ($edLevel) {
                return [
                    'value' => $edLevel->getKey(),
                    'label' => $edLevel->name
                ];
            })
            ->toArray();
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

    public function getTestProperty()
    {
        return Test::whereUuid($this->testId)->first();
    }

    public function handleCheckboxClick($questionId)
    {
//        if ($this->isQuestionInTest($questionId)) {
//            $this->emitTo('drawer.cms', 'deleteQuestionByQuestionId', $questionId);
//            $this->removeQuestionFromTest($questionId);
//            return;
//        }

        $this->addQuestionToTest($questionId);
    }

    public function addQuestionToTest($questionId)
    {
        if ($this->inGroup) {
            $response = $this->peformControllerActionForSubQuestion($questionId);
        } else {
            $response = $this->performControllerActionForQuestion($questionId);
        }

        if ($response->getStatusCode() == 200) {
            $this->addedQuestionIds[json_decode($response->getContent())->question_id] = 0;
            $this->dispatchBrowserEvent('question-added');
        }
    }

    private function getQuestionIdsThatAreAlreadyInTest()
    {
        return $this->test->getQuestionOrderList();
    }

    private function removeQuestionFromTest($questionId)
    {
        collect($this->addedQuestionIds)->reject(function ($id) use ($questionId) {
            return $id === $questionId;
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
        return Question::filtered($this->getFilters())
            ->where(function ($query) {
                $query->where('scope', '!=', 'cito')
                    ->orWhereNull('scope');
            })
            ->with([
                'subject:id,name',
            ])
            ->orderby('created_at', 'desc')
            ->distinct();
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
            "discuss"           => 0,
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
            $this->filters[$tab] = [
                'search'               => '',
                'subject_id'           => [$this->test->subject_id],
                'education_level_year' => [$this->test->education_level_year],
                'education_level_id'   => [$this->test->education_level_id],
                'without_groups'       => '',
                'author_id'            => []
            ];
        });
    }

    public function openDetail($questionUuid)
    {
        $this->emit('openModal', 'teacher.question-detail-modal', ['questionUuid' => $questionUuid]);
    }
}