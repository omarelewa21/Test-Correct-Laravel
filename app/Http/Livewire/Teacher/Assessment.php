<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use tcCore\Exceptions\AssessmentException;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Interfaces\CollapsableHeader;
use tcCore\TestTake;

class Assessment extends Component implements CollapsableHeader
{
    /*Template properties*/
    public bool $headerCollapsed = false;
    public array $assessmentContext = [
        'skipCoLearningDiscrepancies' => false,
        'skippedCoLearning'           => false,
        'assessedAt'                  => null,
        'assessmentType'              => null,
    ];

    /*Computed*/
    protected $queryString = [
        'referrer' => ['except' => ''],
        'qi'       => ['except' => ''],
        'ai'       => ['except' => ''],
    ];
    public string $referrer = '';
    public string $qi = ''; /* Question index */
    public string $ai = ''; /* Answer index */

    /*Component properties*/
    protected bool $skipBooted = false;
    public string $testName;
    public string $testTakeUuid;
    public bool $openOnly;
    public int $questionIndex = 1;
    public int $answerIndex = 1;

    public $questionCount;
    public $studentCount;
    public $currentAnswer;
    public $currentQuestion;

    protected $answers;
    protected $questions;
    protected $testTakeData;

    public $score;
    public $toggle;

    /*        [
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
        ];*/

    public function mount(TestTake $testTake): void
    {
        $this->testTakeUuid = $testTake->uuid;
        $this->setTemplateVariables($testTake);

        $this->headerCollapsed = Session::has("assessment-started-$this->testTakeUuid");

        if ($this->headerCollapsed) {
            $this->skipBootedMethod();
            $this->setTestTakeData();
            $this->startAssessment();
        }
    }

    public function render()
    {
        return view('livewire.teacher.assessment')->layout('layouts.assessment');
    }

    public function booted(): void
    {
        if ($this->skipBooted) {
            return;
        }

        if ($this->headerCollapsed) {
            $this->setTestTakeData();
        }
    }

    /**
     * @throws AssessmentException
     */
    public function handleHeaderCollapse($args): bool
    {
        [$assessmentType, $reset] = $this->validateStartArguments($args);

        TestTake::whereUuid($this->testTakeUuid)
            ->update([
                'assessed_at'     => now(),
                'assessment_type' => $assessmentType
            ]);

        $this->setTestTakeData();

        $this->startAssessment();

        Session::put("assessment-started-$this->testTakeUuid", $args);

        return $this->headerCollapsed = true;
    }

    public function redirectBack()
    {
        Session::forget("assessment-started-$this->testTakeUuid");

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

    public function loadAnswer($value)
    {
        $this->ai = $value;
        $this->answerIndex = $value - 1;

        $answersForQuestion = $this->answers->where('question_id', $this->currentQuestion->getKey())->values();
        // 1 Are there answers?
        // 2 Is there an answer for this index?
        $this->currentAnswer = $answersForQuestion[$this->answerIndex];

        $this->makeToggleProperties();

        return $value;
    }

    public function loadQuestion($value)
    {
        $this->qi = $value;
        $this->questionIndex = $value - 1;

        $this->currentQuestion = $this->questions[$this->questionIndex];

        $this->loadAnswer($this->ai);

        return $value;
    }

    private function setTemplateVariables(TestTake $testTake): void
    {
        $this->testName = $testTake->test->name;

        $this->assessmentContext = [
            'skippedCoLearning' => !$testTake->skipped_discussion,
            'assessedAt'        => filled($testTake->assessed_at) ? str($testTake->assessed_at->translatedFormat('j M Y'))->replace('.', '') : null,
            'assessmentType'    => $testTake->assessment_type,
        ];
        $this->openOnly = $testTake->assessment_type === 'OPEN_ONLY';

        $this->questionCount = $testTake->test()->select('id')->first()->loadCount('testQuestions')->test_questions_count;
        $this->studentCount = $testTake->loadCount('testParticipants')->test_participants_count;
    }

    /**
     * @return void
     */
    public function setTestTakeData(): void
    {
        $this->testTakeData = cache()->remember("assessment-data-$this->testTakeUuid", now()->addDays(3), function () {
            return TestTake::whereUuid($this->testTakeUuid)
                ->with([
                    'testParticipants:id,uuid,test_take_id,user_id',
                    'testParticipants.answers:id,uuid,test_participant_id,question_id,json,final_rating,done',
                    'test:id',
                    'test.testQuestions:id,test_id,question_id',
                    'test.testQuestions.question',
                ])
                ->first();
        });

        $this->answers = $this->testTakeData->testParticipants->flatMap(function ($participant) {
            return $participant->answers->map(function ($answer) {
                return $answer;
            });
        })->sortBy('question_id');

        $this->questions = $this->testTakeData->test->listOfTakeableTestQuestions()->map->question;
    }

    private function startAssessment(): void
    {
        if (blank($this->qi)) $this->qi = '1';
        if (blank($this->ai)) $this->ai = '1';

        $this->loadQuestion($this->qi);
    }

    private function skipBootedMethod(): void
    {
        $this->skipBooted = true;
    }

    public function getNeedsQuestionSectionProperty(): bool
    {
        return !$this->currentQuestion->isSubType('TrueFalse');
    }

    private function makeToggleProperties(): void
    {
        if ($this->currentQuestion->isType('MultipleChoice')) {
            if($this->currentQuestion->isSubType('MultipleChoice')) {
                for ($i = 0; $i <= $this->currentQuestion->selectable_answers; $i++) {
//                    $this->toggle[$i] = null;
                }
            }

            if ($this->currentQuestion->isSubType('TrueFalse')) {
//                $this->toggle = null;
            }
        }
    }
}