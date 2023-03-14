<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use tcCore\Exceptions\AssessmentException;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Interfaces\CollapsableHeader;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

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
    public bool $questionPanel = true;
    public bool $answerPanel = true;
    public bool $answerModelPanel = true;
    public bool $groupPanel = true;

    /*Computed*/
    protected $queryString = [
        'referrer' => ['except' => ''],
        'qi'       => ['except' => ''],
        'ai'       => ['except' => ''],
    ];
    public string $referrer = '';
    public string $qi = ''; /* Question index */
    public string $ai = ''; /* Answer index */
    /**
     * @return mixed
     */
    public function getAnswersForCurrentQuestion()
    {
        return $this->answers->where('question_id', $this->currentQuestion->getKey())->values();
    }

    protected function getListeners()
    {
        return [
            'accordion-update' => 'handlePanelActivity'
        ];
    }

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

    public $currentGroup;

    protected $answers;
    protected $questions;
    protected $groups;
    protected $testTakeData;

    public $score;

    public function mount(TestTake $testTake): void
    {
        $this->testTakeUuid = $testTake->uuid;

        $this->headerCollapsed = Session::has("assessment-started-$this->testTakeUuid");
        $this->setTestTakeData();

        if ($this->headerCollapsed) {
            $this->skipBootedMethod();
            $this->startAssessment();
        }
        $this->setTemplateVariables($testTake);
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
    private function validateStartArguments($args): array
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
        $answersForQuestion = $this->getAnswersForCurrentQuestion();

        // 1 Are there answers?
        if ($answersForQuestion->isEmpty()) {
//            $this->loadQuestion($value + 1, true);
            throw new AssessmentException('Geen antwoorden voor vraag: ' . $this->currentQuestion->getKey());
        }

        // 2 Is there an answer for this index?
        $index = $value - 1;
        if ($answersForQuestion->has($index)) {
            $this->currentAnswer = $answersForQuestion[$index];
            $this->answerIndex = $index;
            $this->ai = $value;
            return $value;
        }

        if ($answer = $answersForQuestion->filter(fn($answer, $key) => $key > $index)->first()) {
            $this->currentAnswer = $answer;
            $this->answerIndex = $answersForQuestion->search($answer);
            $this->ai = $this->answerIndex + 1;

            return $this->ai;
        }

        throw new AssessmentException('Geen antwoord voor vraag:' . $this->currentQuestion->getKey() . ' antwoord: ' . $value);

        return $value;
    }

    public function loadQuestion($value)
    {
        $newIndex = $value - 1;

        if ($this->questions->has($newIndex)) {
            $nextQuestion = $this->questions->get($newIndex);
            $abc = $this->answers->where('question_id', $nextQuestion->id)->values()->get($this->ai);
            if ($abc) {
                $this->currentQuestion = $nextQuestion;
                $this->qi = $value;
                $this->questionIndex = $newIndex;
                $this->loadAnswer($this->ai);
                $this->handleGroupQuestion();
                return $value;
            }
        }

        $increasingIndex = $value > (int)$this->qi;
        $newQuestionId = $this->answers->where('test_participant_id', $this->currentAnswer->test_participant_id)
            ->values()
            ->filter(fn($answer, $key) => $increasingIndex ? $key >= $value - 1 : $key < $value - 1)
            ->when(($increasingIndex), fn($answers) => $answers->first(), fn($answers) => $answers->last())
            ->question_id;

        $this->currentQuestion = $this->questions->where('id', $newQuestionId)->first();
        $index = $this->questions->search($this->currentQuestion);
        $this->qi = $index + 1;
        $this->questionIndex = $index;
        $this->loadAnswer($this->ai);

        return $this->qi;
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

        $this->questionCount = $this->questions->count();
        $this->studentCount = $testTake->testParticipants()->where('test_take_status_id', '>', TestTakeStatus::STATUS_TAKING_TEST)->count();
    }

    /**
     * @return void
     */
    private function setTestTakeData(): void
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

        $this->groups = $this->testTakeData->test->testQuestions->map(fn($testQuestion) => $testQuestion->question->isType('Group') ? $testQuestion->question : null)->filter();
        $this->questions = $this->testTakeData->test->testQuestions->flatMap(function ($testQuestion) {
            $testQuestion->question->loadRelated();
            if ($testQuestion->question->type === 'GroupQuestion') {
                $groupQuestion = $testQuestion->question;
                return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($groupQuestion) {
                    $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                    return $item->question;
                });
            }
            return collect([$testQuestion->question]);
        });
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
        $types = collect(['CompletionQuestion']);
        $subTypes = collect(['TrueFalse']);
        if ($types->contains($this->currentQuestion->type)) {
            return false;
        }
        if ($subTypes->contains($this->currentQuestion->subtype)) {
            return false;
        }

        return true;
    }

    /**
     * @param $panelData
     * @return void
     * @throws AssessmentException
     */
    public function handlePanelActivity($panelData): void
    {
        $panelName = str($panelData['key'])->camel()->append('Panel')->value();
        if (!property_exists($this, $panelName)) {
            throw new AssessmentException('Panel update for unknown panel property.');
        }

        $this->$panelName = $panelData['value'];
    }

    private function handleGroupQuestion()
    {
        if (!$this->currentQuestion->belongs_to_groupquestion_id) {
            $this->currentGroup = null;
            return;
        }

        $this->currentGroup = $this->groups->where('id', $this->currentQuestion->belongs_to_groupquestion_id)->first();
    }
}