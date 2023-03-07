<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Livewire\Component;
use tcCore\Exceptions\AssessmentException;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Interfaces\CollapsableHeader;
use tcCore\TestTake;

class Assessment extends Component implements CollapsableHeader
{
    /*Template booleans*/
    public bool $headerCollapsed = false;
    public bool $skipCoLearningDiscrepancies = false;
    public bool $skippedCoLearning = false;

    /*Computed*/
    protected $queryString = ['referrer' => ['except' => '']];
    public string $referrer = '';

    /*Component properties*/
    public string $testName;
    public string $testTakeUuid;
    public $questionsOrderList = [];
    public int $assessingAnswerIndex = 1;

    public $questionCount;
    public $studentCount;
    public $answers = [
        's1' => [
            '1' => 'a',
            '2' => 'b',
            '3' => 'c',
        ],
        's2' => [
            '1' => 'a',
            '2' => 'b',
            '3' => 'c',
        ],
        's3' => [
            '1' => 'a',
            '2' => 'b',
            '3' => 'c',
        ],
        's4' => [
            '1' => 'a',
            '2' => 'b',
            '3' => 'c',
        ],
        's5' => [
            '1' => 'a',
            '2' => 'b',
            '3' => 'c',
        ],
    ];

    public function mount(TestTake $testTake): void
    {
        $this->testName = $testTake->test->name;
        $this->testTakeUuid = $testTake->uuid;
        $this->skippedCoLearning = !$testTake->skipped_discussion;

        if ($this->headerCollapsed) {
            $this->handleHeaderCollapse(['ALL', true]);
        }
    }

    public function render()
    {
        return view('livewire.teacher.assessment')
            ->layout('layouts.assessment');
    }

    /**
     * @throws AssessmentException
     */
    public function handleHeaderCollapse($args): bool
    {
        [$assessmentType, $reset] = $this->validateStartArguments($args);
//        $this->questionsOrderList = collect(
//            TestTake::whereUuid($this->testTakeUuid)
//                ->first()
//                ->test
//                ->getQuestionOrderListWithDiscussionType()
//        );

//        $testTakeData = TestTake::whereUuid($this->testTakeUuid)
//            ->with([
//                'testParticipants:id,uuid,test_take_id,user_id',
//                'testParticipants.answers:id,uuid,test_participant_id,question_id,json,final_rating',
//            ])
//            ->first();
//        $testParticipants = $testTakeData->testParticipants;
//        $answers = $testParticipants->mapWithKeys(function ($participant) {
//            return [$participant->uuid => collect($participant->answers)];
//        });



        collect($this->answers)->each(function($student, $key) {
            $q = count($this->answers[$key]);
            if ($q > $this->questionCount) {
                $this->questionCount = $q;
            }
        });
        $this->studentCount = count($this->answers);


        return $this->headerCollapsed = true;
    }

    public function redirectBack()
    {
        if ($this->referrer === 'cake' || blank($this->referrer)) {
            return CakeRedirectHelper::redirectToCake('test_takes.view', $this->testTakeUuid);
        }

        return redirect()->route($this->referrer, 'norm');
    }

    /**
     * @param $args
     * @return array
     * @throws AssessmentException
     */
    public function validateStartArguments($args): array
    {
        $allowedTypes = ['ALL', 'OPEN_ONLY'];

        [$assessmentType, $reset] = $args;
        if (!in_array($assessmentType, $allowedTypes, true)) {
            throw new AssessmentException('Assessment type not allowed');
        }
        return $args;
    }

    public function loadAnswer($value, $property)
    {
        logger([$value, $property]);
        return $value;
    }

    public function loadStudent($value, $property)
    {
        logger([$value, $property]);

        return $value;
    }
}