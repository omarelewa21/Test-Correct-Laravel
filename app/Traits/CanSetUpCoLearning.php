<?php

namespace tcCore\Traits;

use Ramsey\Uuid\Uuid;
use tcCore\Http\Livewire\Student\TestTake;
use tcCore\Http\Livewire\Teacher\Cms\TypeFactory;
use tcCore\Question;
use tcCore\TestTakeQuestion;
use tcCore\View\Components\Partials\Header\CoLearningTeacher;

trait CanSetUpCoLearning
{
    public int $setupStep = 1;
    private int $setupMaxSteps = 2;

    public string $setUpWireKey;

    //sorting data step 2
    public $setupCoLearningSortField = 'test_index';
    public $setupCoLearningSortDirection = 'asc';
    private $setupSortableFields = ['test_index', 'question_type_name', 'p_value'];

    protected $questionsSetUpOrderList;
    public bool $testHasGroupQuestions;

    protected $queryStringCanSetUpCoLearning = [
        'setupStep' => ['except' => 1, 'as' => 'step'],
    ];

    public function nextSetupStep()
    {
        $this->setupStep += 1;
        $this->validateSetupStep();
    }

    public function previousSetupStep()
    {
        $this->setupStep -= 1;
        $this->validateSetupStep();
    }

    private function validateSetupStep()
    {
        if($this->setupStep < 1 || $this->setupStep > $this->setupMaxSteps) {
            $this->setupStep = 1;
        }
    }

    public function setTableWireKey(): void
    {
        $this->setUpWireKey = sprintf('table-%s-%s-%s',
                                      $this->setupCoLearningSortField,
                                      $this->setupCoLearningSortDirection,
                                      md5($this->questionsSetUpOrderList->map->checked->implode(';')),
        );
    }

    private function getExpandedQuestionList()
    {
        if(!isset($this->questionsSetUpOrderList)) {
            $this->questionsSetUpOrderList = collect($this->testTake->test->getQuestionOrderListExpanded());
        }

        return $this->sortSetupQuestionOrderList();
    }

    public function getTestTakeQuestions()
    {
        return TestTakeQuestion::where('test_take_id', $this->testTake->getKey())
                       ->get();
    }

    public function updateQuestionsChecked($questionTypeFilter = 'all')
    {
        $enabledQuestions = $this->getSetUpData()
            ->filter(fn($item) => !$item['disabled']);

        $questionsCheckedList = $enabledQuestions
            ->when(value   : $questionTypeFilter === "open",
                   callback: function ($collection) {
                    return $collection->filter(fn($item) => $item['open_question']);
                }
            );

        $questionsNotCheckedList = $enabledQuestions
            ->when(value   : $questionTypeFilter === "open",
                   callback: function ($collection) {
                    return $collection->filter(fn($item) => !$item['open_question']);
                },
                   default : fn($collection) => collect()
            );

        $this->upsertTestTakeQuestions($questionsCheckedList);

        $this->deleteTestTakeQuestions($questionsNotCheckedList);

        $this->setTableWireKey();
    }

    protected function upsertTestTakeQuestions($questionList)
    {
        $questionIds = $questionList->pluck('question_id');

        // check if it needs to be restored
        TestTakeQuestion::onlyTrashed()
            ->whereIn('question_id', $questionIds)
            ->restore();

        // get all existing records
        $existingRecords = TestTakeQuestion::withTrashed()
            ->whereIn('question_id', $questionIds)
            ->pluck('question_id')
            ->keyBy(fn($item) => $item);

        // compare existing records with question list to get missing records
        $missingTestTakeQuestions = $questionList
            ->diffKeys($existingRecords)
            ->map(function ($item) {
                return [
                    'test_take_id' => $this->testTake->getKey(),
                    'question_id'  => $item['question_id'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                    'uuid'         => Uuid::uuid4(),
                ];
            })->toArray();

        TestTakeQuestion::insert($missingTestTakeQuestions);
    }

    protected function deleteTestTakeQuestions($questionList)
    {
        TestTakeQuestion::whereIn('question_id', $questionList->pluck('question_id'))
            ->delete();
    }

    public function toggleQuestionChecked($questionUuid)
    {
        // if record exists, it is checked.
        // if record exists and discussed is true, it is a permanent green circle (no longer a checkbox)
        // if record does not exist, it is not checked.
        $question = Question::whereUuid($questionUuid)
                            ->first();

        $testTakeQuestion = TestTakeQuestion::where('test_take_id', $this->testTake->getKey())
                                  ->where('question_id', $question->getKey())
                                  ->withTrashed()
                                  ->first();

        if($testTakeQuestion?->trashed()) {
            $testTakeQuestion->restore();
            return;
        }

        if($testTakeQuestion) {
            $testTakeQuestion->delete();
            return;
        }

        TestTakeQuestion::create([
            'test_take_id' => $this->testTake->getKey(),
            'question_id' => $question->getKey(),
        ]);
    }

    private function sortSetupQuestionOrderList()
    {
        if($this->setupCoLearningSortDirection === 'desc') {
            return $this->questionsSetUpOrderList->sortByDesc($this->setupCoLearningSortField);
        }
        if($this->setupCoLearningSortDirection === 'asc') {
            return $this->questionsSetUpOrderList->sortBy($this->setupCoLearningSortField);
        }

    }

    public function changeSetupQuestionsSorting($field)
    {
        in_array($field, $this->setupSortableFields) || abort(500, "Field $field is not sortable");

        if($this->setupCoLearningSortField === $field) {
            $this->setupCoLearningSortDirection = $this->setupCoLearningSortDirection === 'asc' ? 'desc' : 'asc';
        }

        if($this->setupCoLearningSortField !== $field) {
            $this->setupCoLearningSortDirection = 'asc';
            $this->setupCoLearningSortField = $field;
        }
    }

    public function getDirectionOfSortField($field) : ?string
    {
        if($field !== $this->setupCoLearningSortField) {
            return null;
        }
        return $this->setupCoLearningSortDirection;
    }

    private function getSetUpData()
    {

        //todo create combined data set to print view data:
        // checked? => true/false               #from relation
        // index => #                           #from questionList
        // questionType => "OpenQuestion" etc.  #from questionList
        // previewQuestionText => "..."         #from questionList
        // PValue => 99%                        #from questionList

        $setupQuestionData = $this->getExpandedQuestionList();

        $groupNumberIterator = 1;
        $groupNumbers = $setupQuestionData->unique('group_question_id')
                             ->whereNotNull('group_question_id')
                             ->mapWithKeys(fn($uniqueGroup) => [$uniqueGroup['group_question_id'] => "G" . $groupNumberIterator++]);

        $testTakeQuestions = $this->getTestTakeQuestions();

        $setupQuestionData = $setupQuestionData->map(function ($questionData) use ($groupNumbers, $testTakeQuestions) {
            $questionData['disabled'] = $questionData['question_type'] === 'InfoscreenQuestion' || $questionData['carousel_question'];
            $questionData['group_number'] = $groupNumbers[$questionData['group_question_id']] ?? null;

            $testTakeQuestion = $testTakeQuestions->where(fn($item) => $item->question_id === $questionData['question_id'])->first();
            $questionData['checked'] = !is_null($testTakeQuestion);
            $questionData['discussed'] = (bool) $testTakeQuestion?->discussed;
            return $questionData;
        });

        $this->testHasGroupQuestions = $setupQuestionData->filter(fn($item) => $item['group_question_id'] !== null)->count() > 0;

        $this->questionsSetUpOrderList = $setupQuestionData;

        $this->setTableWireKey();

        return $setupQuestionData;
    }
}