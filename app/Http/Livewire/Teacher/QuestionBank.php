<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\Question;
use tcCore\Subject;
use tcCore\Test;

class QuestionBank extends Component
{
    const ITEM_INCREMENT = 15;

    const CONTEXT_PERSONAL = 'me';
    const CONTEXT_SCHOOL = '';

    protected $queryString = ['testId'];

    public $testId;
    public $filters = [
        'search'               => '',
        'subject_id'           => [],
        'education_level_year' => [],
        'education_level_id'   => [],
        'source'               => self::CONTEXT_PERSONAL,
        'without_groups'       => ''
    ];

    public $addedQuestionIds = [];
    public $itemsPerPage;

    public $inGroup = false;

    public function mount()
    {
        $this->itemsPerPage = QuestionBank::ITEM_INCREMENT;
        $this->addedQuestionIds = $this->getQuestionIdsThatAreAlreadyInTest();
    }

    public function render()
    {
        return view('livewire.teacher.question-bank');
    }

    public function getQuestionsProperty()
    {
        return Question::filtered($this->getFilters())
            ->where(function ($query) {
                $query->where('scope', '!=', 'cito')
                    ->orWhereNull('scope');
            })
//            ->where(function ($query) {
//                $query->whereNotIn('id', $this->getQuestionIdsThatAreAlreadyInTest());
//            })
            ->with([
                'questionAttainments',
                'questionAttainments.attainment',
                'tags',
                'authors',
                'subject:id,base_subject_id,name',
                'subject.baseSubject:id,name'
            ])
            ->distinct()
            ->take($this->itemsPerPage)
            ->get();
    }

    private function getFilters()
    {
        return collect($this->filters)->reject(function ($filter) {
            return empty($filter);
        })->toArray();
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
            })->toArray();
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
            })->toArray();
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

    public function handleCheckboxClick($questionId)
    {
        if ($this->isQuestionInTest($questionId)) {
            $this->emitTo('drawer.cms', 'deleteQuestionByQuestionId', $questionId);
            return $this->removeQuestionFromTest($questionId);
        }

        $this->addQuestionToTest($questionId);
    }

    public function addQuestionToTest($questionId)
    {

        $this->addedQuestionIds[] = $questionId;

        $requestParams = [
            'test_id'           => $this->test->getKey(),
            'order'             => 0,
            'maintain_position' => 0,
            'discuss'           => 1,
            'closeable'         => 0,
            'question_id'       => $questionId,
        ];

        $response = (new TestQuestionsController)->store(new CreateTestQuestionRequest($requestParams));

        if ($response->getStatusCode() == 200) {
            $this->dispatchBrowserEvent('question-added');
        }

    }

    private function getQuestionIdsThatAreAlreadyInTest()
    {
        return $this->test
            ->testQuestions()
            ->pluck('question_id')
            ->toArray();
    }

    public function getTestProperty()
    {
        return Test::whereUuid($this->testId)->first();
    }

    private function removeQuestionFromTest($questionId)
    {
        collect($this->addedQuestionIds)->reject(function ($id) use ($questionId) {
            return $id === $questionId;
        });
    }

    public function isQuestionInTest($questionId)
    {
        return collect($this->addedQuestionIds)->contains($questionId);
    }

    public function showMore()
    {
        $this->itemsPerPage += QuestionBank::ITEM_INCREMENT;
    }

    public function updatedFilters($name, $value)
    {
        $this->resetItemsPerPage();
    }

    private function resetItemsPerPage()
    {
        $this->itemsPerPage = QuestionBank::ITEM_INCREMENT;
    }

    public function setSource($source)
    {
        if ($source === 'personal') {
            return $this->filters['source'] = self::CONTEXT_PERSONAL;
        }

        return $this->filters['source'] = self::CONTEXT_SCHOOL;
    }

    public function updatedInGroup($value)
    {
        $this->filters['without_groups'] = $value;
    }
}