<?php

namespace tcCore\Traits;

use tcCore\View\Components\Partials\Header\CoLearningTeacher;

trait CanSetUpCoLearning
{
    public int $step = 1;
    private int $amountOfSteps = 2;

    protected $queryStringCanSetUpCoLearning = [
        'step' => ['except' => 1],
    ];

    public function nextStep()
    {
        $this->step += 1;
        $this->validateStep();
    }

    public function previousStep()
    {
        $this->step -= 1;
        $this->validateStep();
    }

    private function validateStep()
    {
        if($this->step < 1 || $this->step > $this->amountOfSteps) {
            $this->step = 1;
        }
    }


    public function getQuestionEnabled($questionId, $testTakeId)
    {
        // todo retrieve relation of testtake/question and return if it exists
        //  if it does not exist, return false
        //  if discussed is true, it is also no longer a checkbox
        //  maybe change function name? or return type?
    }

    public function setQuestionEnabled()
    {
        // todo change relation of testtake/question, create or restore record
        //  maybe change function name? or return type?
        //  also update discussed status?
    }

    public function orderSetUpQuestions($field, $direction)
    {
        if($direction === 'desc') {
            $this->questionsSetUpOrderList = $this->questionsSetUpOrderList->sortByDesc($field);
            return;
        }
        if($direction === 'asc') {
            $this->questionsSetUpOrderList = $this->questionsSetUpOrderList->sortBy($field);
        }
    }

    private function getSetUpData()
    {

        //todo create combined data set to print view data:
        // checked? => true/false               #from relation
        // index => #                           #from questionList
        // questionType => "OpenQuestion" etc.  #from questionList
        // previewQuestionText => "..."         #from questionList
        // PValue => 99%                        #from questionList

        $temp = $this->getExpandedQuestionList();

        $groupNumberIterator = 1;
        $groupNumbers = $temp->unique('group_question_id')
                             ->whereNotNull('group_question_id')
                             ->mapWithKeys(fn($uniqueGroup) => [$uniqueGroup['group_question_id'] => "G" . $groupNumberIterator++]);

        $temp = $temp->map(function ($questionData) use ($groupNumbers) {
            $questionData['disabled'] = $questionData['question_type'] === 'InfoscreenQuestion' || $questionData['carousel_question'];
            $questionData['group_number'] = $groupNumbers[$questionData['group_question_id']] ?? null;
            return $questionData;
        });

        $this->testHasGroupQuestions = $temp->filter(fn($item) => $item['group_question_id'] !== null)->count() > 0;

        // todo sort using the following:
//        $this->orderSetUpQuestions('p_value', 'asc');
//        $this->orderSetUpQuestions('question_id', 'asc');
//        $this->orderSetUpQuestions('question_type_name', 'asc');
        dd($this->questionsSetUpOrderList);

        return $temp;


        //if a record exists, it is checked.
        // if a record is discussed, it is a permanent green circle (no longer a checkbox)

        // TestTakeRelationRecord => [▼
        //      "test_take_id" => 1
        //      "question_id" => 592
        //      "discussed" => false
        //    ]


    }
}