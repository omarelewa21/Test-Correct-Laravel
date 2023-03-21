<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Exceptions\AssessmentException;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Interfaces\CollapsableHeader;
use tcCore\Question;
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
    public string $testName;

    /*Query string properties*/
    protected $queryString = [
        'referrer'                => ['except' => '', 'as' => 'r'],
        'questionNavigationValue' => ['except' => '', 'as' => 'qi'],
        'answerNavigationValue'   => ['except' => '', 'as' => 'ai'],
    ];
    public string $referrer = '';
    public string $questionNavigationValue = '';
    public string $answerNavigationValue = '';

    /* Navigation properties */
    public int $questionIndex = 1;
    public int $answerIndex = 1;
    public $questionCount;
    public $lastQuestionForStudent;
    public $firstQuestionForStudent;
    public $studentCount;
    public $firstAnswerForQuestion;
    public $lastAnswerForQuestion;

    /* Data properties filled from cache */
    protected $testTakeData;
    protected $answers;
    protected $answerRatings;
    protected $questions;
    protected $groups;
    protected $students;

    /* Context properties */
    public $currentAnswer;
    public $currentQuestion;
    public $currentGroup;
    public $score = null;

    protected bool $skipBooted = false;

    public string $testTakeUuid;
    public bool $openOnly;

    /* Livewire methods */
    protected function getListeners()
    {
        return [
            'accordion-update' => 'handlePanelActivity'
        ];
    }

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

    public function booted(): void
    {
        if ($this->skipBooted) {
            return;
        }

        if ($this->headerCollapsed) {
            $this->setTestTakeData();
        }
    }

    public function render()
    {
        return view('livewire.teacher.assessment')->layout('layouts.assessment');
    }

    /* Computed properties */
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

    public function getFastScoringOptionsProperty()
    {
        $score = $this->currentQuestion->score;
        $middle = (int)round($score / 2);
        $top = (int)$score;

        $options = collect([
            [
                'title'  => __('assessment.insufficient'),
                'points' => "0",
                'text'   => __('assessment.insufficient_text'),
                'value'  => 0,
            ]
        ]);
        if ($score < 2) {
            return $options->push([
                'title'  => __('assessment.sufficient'),
                'points' => "+" . $top,
                'text'   => __('assessment.great_text'),
                'value'  => $top,
            ]);
        }

        return $options->push(
            [
                'title'  => __('assessment.sufficient'),
                'points' => "+" . $middle,
                'text'   => __('assessment.sufficient_text'),
                'value'  => $middle,
            ],
            [
                'title'  => __('assessment.great'),
                'points' => "+" . $top,
                'text'   => __('assessment.great_text'),
                'value'  => $top,
            ]);
    }

    public function getShowAutomaticallyScoredToggleProperty(): bool
    {
        if ($this->currentQuestion->isType('Infoscreen')) {
            return false;
        }
        return $this->currentAnswerHasRatingsOfType(AnswerRating::TYPE_SYSTEM);
    }

    public function getShowCoLearningScoreToggleProperty(): bool
    {
        return $this->currentAnswer->answerRatings->where('type', AnswerRating::TYPE_STUDENT)->isNotEmpty()
            || $this->currentQuestion->isType('Infoscreen');
    }

    public function getShowFastScoringProperty(): bool
    {
        return !$this->currentQuestion->isType('Infoscreen');
    }

    public function getShowScoreSliderProperty(): bool
    {
        return !$this->currentQuestion->isType('Infoscreen');
    }

    /* Event listener methods */
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

    /* Public accessible methods */
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
     * @throws AssessmentException
     */
    public function loadAnswer($value, $action = null, $internal = false): array
    {
        $answersForQuestion = $this->getAnswersForCurrentQuestion();

        if ($answersForQuestion->isEmpty()) {
            throw new AssessmentException('Geen antwoorden voor vraag: ' . $this->currentQuestion->getKey());
        }

        $answerForCurrentStudent = $answersForQuestion->first(fn($answer) => $answer->test_participant_id === $this->students->get($value - 1));
        if (!$answerForCurrentStudent) {
            $answerForCurrentStudent = $this->retrieveAnswerBasedOnAction($action, $answersForQuestion);
            $value = $this->students->search($answerForCurrentStudent->test_participant_id) + 1;
        }

        $this->setComponentAnswerProperties($answerForCurrentStudent, $value);

        $this->lastAnswerForQuestion = $this->students->search($answersForQuestion->last()->test_participant_id) + 1;
        $this->firstAnswerForQuestion = $this->students->search($answersForQuestion->first()->test_participant_id) + 1;

        if (!$internal) {
            $this->dispatchUpdateQuestionNavigatorEvent();
        }

        $this->score = $this->figureOutAnswerScore();

        return [
            'index' => $this->answerNavigationValue,
            'last'  => $this->lastAnswerForQuestion,
            'first' => $this->firstAnswerForQuestion,
        ];
    }

    /**
     * @throws AssessmentException
     */
    public function loadQuestion($value, $action = null): array
    {
        $newIndex = $value - 1;

        if ($nextQuestion = $this->questions->get($newIndex)) {
            $hasAnswerForNextQuestion = $this->answers->where('question_id', $nextQuestion->id)
                ->where('test_participant_id', $this->getCurrentParticipantId())
                ->first();
            if ($hasAnswerForNextQuestion) {
                return $this->returnFromLoadQuestion($nextQuestion, $newIndex);
            }
        }

        $nextQuestion = $this->retrieveQuestionBasedOnAction($action);
        $newIndex = $this->questions->search($nextQuestion);

        return $this->returnFromLoadQuestion($nextQuestion, $newIndex);
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
        $this->studentCount = $this->students->count();
    }

    /**
     * @return void
     */
    private function setTestTakeData(): void
    {
        $this->testTakeData = cache()->remember("assessment-data-$this->testTakeUuid", now()->addDays(3), function () {
            return TestTake::whereUuid($this->testTakeUuid)
                ->with([
                    'testParticipants:id,uuid,test_take_id,user_id,test_take_status_id',
                    'testParticipants.answers:id,uuid,test_participant_id,question_id,json,final_rating,done',
                    'testParticipants.answers.answerRatings:id,answer_id,type,rating,advise',
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
        })
            ->sortBy('order')
            ->sortBy('test_participant_id')
            ->values();

        $this->groups = $this->testTakeData->test->testQuestions->map(fn($testQuestion) => $testQuestion->question->isType('Group') ? $testQuestion->question : null)->filter();
        $this->questions = $this->testTakeData->test->testQuestions->sortBy('order')->flatMap(function ($testQuestion) {
            $testQuestion->question->loadRelated();
            if ($testQuestion->question->type === 'GroupQuestion') {
                $groupQuestion = $testQuestion->question;
                return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($groupQuestion) {
                    $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                    return $item->question;
                });
            }
            return collect([$testQuestion->question]);
        })->values();
        $this->students = $this->testTakeData->testParticipants->where('test_take_status_id', '>', TestTakeStatus::STATUS_TAKING_TEST)->sortBy('id')->pluck('id');
    }

    private function startAssessment(): void
    {
        $this->initializeNavigationProperties();

        $this->loadQuestion($this->questionNavigationValue);
    }

    private function skipBootedMethod(): void
    {
        $this->skipBooted = true;
    }

    private function handleGroupQuestion()
    {
        if (!$this->currentQuestion->belongs_to_groupquestion_id) {
            $this->currentGroup = null;
            return;
        }

        $this->currentGroup = $this->groups->where('id', $this->currentQuestion->belongs_to_groupquestion_id)->first();
    }

    /**
     * @return mixed
     */
    private function getAnswersForCurrentQuestion(): Collection
    {
        return $this->answers->where('question_id', $this->currentQuestion->getKey())->values();
    }

    /**
     * @return mixed
     */
    private function getAvailableAnswersForCurrentStudent(): Collection
    {
        return $this->answers->where('test_participant_id', $this->currentAnswer->test_participant_id)->values();
    }

    /**
     * @return int
     */
    private function getLastQuestionsCountForCurrentStudent(): int
    {
        return $this->questions->search(
                $this->questions->where('id', $this->getAvailableAnswersForCurrentStudent()->last()->question_id)->first()
            ) + 1;
    }

    /**
     * @return int
     */
    private function getFirstQuestionsCountForCurrentStudent(): int
    {
        return $this->questions->search(
                $this->questions->where('id', $this->getAvailableAnswersForCurrentStudent()->first()->question_id)->first()
            ) + 1;
    }

    private function setComponentQuestionProperties(Question $newQuestion, int $index): void
    {
        $this->currentQuestion = $newQuestion;
        $this->questionNavigationValue = $index + 1;
        $this->questionIndex = $index;
        $this->handleGroupQuestion();
    }

    private function setComponentAnswerProperties(Answer $answer, int $index): void
    {
        $this->currentAnswer = $answer;
        $this->answerNavigationValue = $index;
        $this->answerIndex = $this->answerNavigationValue - 1;
    }

    /**
     * @param Question $nextQuestion
     * @param int $newIndex
     * @return array
     * @throws AssessmentException
     */
    private function returnFromLoadQuestion(Question $nextQuestion, int $newIndex): array
    {
        $this->setComponentQuestionProperties($nextQuestion, $newIndex);
        $this->dispatchUpdateAnswerNavigatorEvent(
            $this->loadAnswer(value: $this->answerNavigationValue, internal: true)
        );

        // Current answer needs to be set before calculating first and last;
        $firstForCurrentStudent = $this->firstQuestionForStudent = $this->getFirstQuestionsCountForCurrentStudent();
        $lastForCurrentStudent = $this->lastQuestionForStudent = $this->getLastQuestionsCountForCurrentStudent();

        return [
            'index' => $this->questionNavigationValue,
            'first' => $firstForCurrentStudent,
            'last'  => $lastForCurrentStudent,
        ];
    }

    private function getCurrentParticipantId()
    {
        return $this->students->get($this->answerNavigationValue - 1);
    }

    /**
     * @param string $action
     * @return Question
     * @throws AssessmentException
     */
    private function retrieveQuestionBasedOnAction(string $action): Question
    {
        $availableAnswers = $this->getAvailableAnswersForCurrentStudent();
        $currentIndex = $availableAnswers->search(fn($item) => $item->question_id === $this->currentQuestion->id);

        $closestAvailableAnswer = $this->getClosestAvailableAnswer($action, $availableAnswers, $currentIndex);

        if (!$closestAvailableAnswer) {
            throw new AssessmentException(sprintf('Cannot find closest question to navigate to based on the "%s" from question position %s', $action, $this->questionNavigationValue));
        }

        return $this->questions->where(
            'id',
            $closestAvailableAnswer->question_id
        )->first();
    }


    private function retrieveAnswerBasedOnAction(string $action, Collection $answers): Answer
    {
        $currentIndex = $answers->search(fn($item) => $item->test_participant_id === $this->getCurrentParticipantId());

        $closestAvailableAnswer = $this->getClosestAvailableAnswer($action, $answers, $currentIndex);

        if (!$closestAvailableAnswer) {
            throw new AssessmentException(sprintf('Cannot find closest answer to navigate to based on "%s" from answer position %s', $action, $this->answerNavigationValue));
        }

        return $closestAvailableAnswer;
    }

    /**
     * @param string $action
     * @param Collection $answers
     * @param int $currentIndex
     * @return Answer|null
     */
    private function getClosestAvailableAnswer(string $action, Collection $answers, int $currentIndex): ?Answer
    {
        if ($action === 'last') return $answers->last();
        if ($action === 'first') return $answers->first();
        if ($action === 'decr') return $answers->filter(fn($answer, $key) => $key < $currentIndex)->last();
        if ($action === 'incr') return $answers->filter(fn($answer, $key) => $key > $currentIndex)->first();
    }

    private function dispatchUpdateAnswerNavigatorEvent(array $answerUpdates): void
    {
        $this->dispatchBrowserEvent('update-navigation', ['navigator' => 'answer', 'updates' => $answerUpdates]);
    }

    private function dispatchUpdateQuestionNavigatorEvent()
    {
        $questionUpdates = [
            'index' => $this->questionNavigationValue,
            'first' => $this->getFirstQuestionsCountForCurrentStudent(),
            'last'  => $this->getLastQuestionsCountForCurrentStudent(),
        ];
        $this->dispatchBrowserEvent('update-navigation', ['navigator' => 'question', 'updates' => $questionUpdates]);
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

    private function initializeNavigationProperties()
    {
        if ($this->validQueryStringPropertiesForNavigation()) {
            return;
        }

        $firstAnswer = $this->answers->first();
        $firstQuestionForAnswer = $this->questions->where('id', $firstAnswer->question_id)->first();

        $this->questionNavigationValue = $this->questions->search($firstQuestionForAnswer) + 1;
        $this->answerNavigationValue = $this->answers->search($firstAnswer) + 1;
    }

    private function validQueryStringPropertiesForNavigation(): bool
    {
        if (blank($this->questionNavigationValue) || blank($this->answerNavigationValue)) return false;

        $question = $this->questions->get((int)$this->questionNavigationValue - 1);
        $answer = $this->answers->where('question_id', $question->id)->values()->get($this->answerNavigationValue - 1);

        return $answer && $question && $answer->question_id === $question->id;
    }

    private function currentIndexIsAnInfoQuestion()
    {
        return $this->questions->get((int)$this->questionNavigationValue - 1)?->first()->isType('Infoscreen');
    }

    private function figureOutAnswerScore(): ?int
    {
        $ratings = $this->currentAnswer->answerRatings;

        return $ratings->where('type', AnswerRating::TYPE_SYSTEM)->first()?->rating;
    }

    private function currentAnswerHasRatingsOfType(string $type): bool
    {
        return $this->currentAnswer->answerRatings->where('type', $type)->isNotEmpty();
    }
}