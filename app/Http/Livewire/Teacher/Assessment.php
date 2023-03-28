<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
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
        'assessIndex'                 => null,
        'totalToAssess'               => 0
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

    /* Lifecycle methods */
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

    public function updatedScore($value)
    {
        $this->updateOrCreateAnswerRating($value);
    }

    /* Computed properties */
    public function getNeedsQuestionSectionProperty(): bool
    {
        $types = collect([/*'CompletionQuestion'*/]);
        $subTypes = collect([/*'TrueFalse'*/]);
        if ($types->contains($this->currentQuestion->type)) {
            return false;
        }
        if ($subTypes->contains($this->currentQuestion->subtype)) {
            return false;
        }

        return true;
    }

    public function getFastScoringOptionsProperty(): Collection
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
        if ($this->hasOnlyTwoFastScoringOptions()) {
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
        if ($this->currentQuestion->isType('Infoscreen')) {
            return false;
        }
        return $this->currentAnswerHasRatingsOfType(AnswerRating::TYPE_STUDENT);
    }

    public function getShowFastScoringProperty(): bool
    {
        return !$this->currentQuestion->isType('Infoscreen');
    }

    public function getShowScoreSliderProperty(): bool
    {
        return !$this->currentQuestion->isType('Infoscreen');
    }

    public function getDrawerScoringDisabledProperty(): bool
    {
        if (!$this->headerCollapsed) return true;

        $types = collect([
            'completionquestion',
            'multiplechoicequestion',
            'matchingquestion',
        ]);
        $subTypes = collect([
            'multi',
            'completion',
            'truefalse',
            'multiplechoice',
            'classify',
            'matching',
        ]);

        if ($types->contains(Str::lower($this->currentQuestion->type))) {
            return $subTypes->contains(Str::lower($this->currentQuestion->subtype));
        }

        return !$this->currentAnswer->isAnswered;
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

        $updates = [
            'assessed_at'     => now(),
            'assessment_type' => $assessmentType
        ];
        if ($reset) {
            $updates['assessing_question_id'] = null;

            AnswerRating::where(
                'test_take_id',
                TestTake::whereUuid($this->testTakeUuid)->first()->getKey()
            )
                ->where('type', AnswerRating::TYPE_TEACHER)
                ->update(['deleted_at' => now()]);
        }
        TestTake::whereUuid($this->testTakeUuid)->update($updates);

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

        if ($nextQuestion = $this->questions->discussionTypeFiltered($this->openOnly)->get($newIndex)) {
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

    /**
     * @throws AssessmentException
     */
    public function next(): bool
    {
        if ($this->finalAnswerReached()) {
            throw new AssessmentException('This should not be possible. Are you a magician?');
        }

        if (!$this->onLastAnswerForQuestion()) {
            $this->dispatchUpdateAnswerNavigatorEvent(
                $this->loadAnswer(value: (int)$this->answerNavigationValue + 1, action: 'incr', internal: true)
            );
            return true;
        }

        if (!$this->onLastQuestionToAssess()) {
            $newQuestionId = $this->questions->get((int)$this->questionNavigationValue)->id;

            $newAnswerIndex = $this->students->search(
                    $this->answers->where('question_id', $newQuestionId)->first()->test_participant_id
                ) + 1;

            $this->answerNavigationValue = $newAnswerIndex;

            $this->dispatchUpdateQuestionNavigatorEvent(
                $this->loadQuestion(value: (int)$this->questionNavigationValue + 1, action: 'incr')
            );
            return true;
        }

        throw new AssessmentException('You somehow managed to get this far? Go get a medal you magnificent beast!');
    }

    /**
     * @throws AssessmentException
     */
    public function previous(): bool
    {
        if ($this->onBeginningOfAssessment()) {
            throw new AssessmentException('You can\'t go back in time you silly goose.');
        }

        if (!$this->onFirstAnswerForQuestion()) {
            $this->dispatchUpdateAnswerNavigatorEvent(
                $this->loadAnswer(value: (int)$this->answerNavigationValue - 1, action: 'decr', internal: true)
            );
            return true;
        }

        if (!$this->onFirstQuestionToAssess()) {
            $newQuestionId = $this->questions->get((int)$this->questionNavigationValue - 2)->id;
            $newAnswerIndex = $this->students->search(
                    $this->answers->where('question_id', $newQuestionId)->last()->test_participant_id
                ) + 1;

            $this->answerNavigationValue = $newAnswerIndex;

            $this->dispatchUpdateQuestionNavigatorEvent(
                $this->loadQuestion(value: (int)$this->questionNavigationValue - 1, action: 'decr')
            );
            return true;
        }

        throw new AssessmentException('Oh my god you are amazing. no one should have been able to do this, but you did!');
    }

    public function finalAnswerReached(): bool
    {
        return $this->onLastQuestionToAssess() && $this->onLastAnswerForQuestion();
    }

    public function onBeginningOfAssessment(): bool
    {
        return $this->onFirstQuestionToAssess() && $this->onFirstAnswerForQuestion();
    }


    /* Private methods */
    private function setTemplateVariables(TestTake $testTake): void
    {
        $this->testName = $testTake->test->name;
        $this->openOnly = $testTake->assessment_type === 'OPEN_ONLY';

        $this->assessmentContext = [
            'skippedCoLearning' => !$testTake->skipped_discussion,
            'assessedAt'        => filled($testTake->assessed_at) ? str($testTake->assessed_at->translatedFormat('j M Y'))->replace('.', '') : null,
            'assessmentType'    => $testTake->assessment_type,
            'assessIndex'       => $this->getAssessedQuestionsCount(),
            'totalToAssess'     => $this->questions->discussionTypeFiltered($this->openOnly)->count(),
        ];

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

        $this->answers = $this->testTakeData->testParticipants
            ->flatMap(fn($participant) => $participant->answers->map(fn($answer) => $answer))
            ->sortBy('order')
            ->sortBy('test_participant_id')
            ->values();

        $this->groups = $this->testTakeData->test->testQuestions
            ->map(fn($testQuestion) => $testQuestion->question->isType('Group') ? $testQuestion->question : null)
            ->filter();

        $this->questions = $this->testTakeData->test->testQuestions
            ->sortBy('order')
            ->flatMap(function ($testQuestion) {
                $testQuestion->question->loadRelated();
                if ($testQuestion->question->type === 'GroupQuestion') {
                    $groupQuestion = $testQuestion->question;
                    return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($groupQuestion) {
                        $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                        $item->question->isDiscussionTypeOpen = !$item->question->canCheckAnswer();
                        return $item->question;
                    });
                }
                $testQuestion->question->isDiscussionTypeOpen = !$testQuestion->question->canCheckAnswer();
                return collect([$testQuestion->question]);
            })
            ->values();

        $this->students = $this->testTakeData->testParticipants->where('test_take_status_id', '>', TestTakeStatus::STATUS_TAKING_TEST)->sortBy('id')->pluck('id');
    }

    private function startAssessment(): void
    {
        $this->initializeNavigationProperties();

        $this->openOnly = $this->testTakeData->fresh()->assessment_type === 'OPEN_ONLY';

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
                $this->questions->where(
                    'id',
                    $this->getAvailableAnswersForCurrentStudent()
                        ->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType())
                        ->last()
                        ->question_id
                )->first()
            ) + 1;
    }

    /**
     * @return int
     */
    private function getFirstQuestionsCountForCurrentStudent(): int
    {
        return $this->questions->search(
                $this->questions->where(
                    'id',
                    $this->getAvailableAnswersForCurrentStudent()
                        ->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType())
                        ->first()
                        ->question_id
                )->first()
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

        $this->firstQuestionForStudent = $this->getFirstQuestionsCountForCurrentStudent();
        $this->lastQuestionForStudent = $this->getLastQuestionsCountForCurrentStudent();

        TestTake::whereUuid($this->testTakeUuid)->update(['assessing_question_id' => $this->currentQuestion->getKey()]);

        return [
            'index' => $this->questionNavigationValue,
            'first' => $this->firstQuestionForStudent,
            'last'  => $this->lastQuestionForStudent,
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
        $answers = $answers->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType());
        if ($action === 'last') return $answers->last();
        if ($action === 'first') return $answers->first();
        if ($action === 'decr') return $answers->filter(fn($answer, $key) => $key < $currentIndex)->last();
        if ($action === 'incr') return $answers->filter(fn($answer, $key) => $key > $currentIndex)->first();
    }

    private function dispatchUpdateNavigatorEvent(string $navigator, array $updates): void
    {
        $this->dispatchBrowserEvent('update-navigation', ['navigator' => $navigator, 'updates' => $updates]);
    }

    private function dispatchUpdateAnswerNavigatorEvent(array $answerUpdates): void
    {
        $this->dispatchUpdateNavigatorEvent('answer', $answerUpdates);
    }

    private function dispatchUpdateQuestionNavigatorEvent(array|null $questionUpdates = null)
    {
        $questionUpdates ??= [
            'index' => $this->questionNavigationValue,
            'first' => $this->getFirstQuestionsCountForCurrentStudent(),
            'last'  => $this->getLastQuestionsCountForCurrentStudent(),
        ];
        $this->dispatchUpdateNavigatorEvent('question', $questionUpdates);
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

        if ($previousId = $this->testTakeData->fresh()->assessing_question_id) {
            $previouslyAssessedQuestion = $this->questions->where('id', $previousId)->first();
            if ($previouslyAssessedQuestion) {
                $this->setNavigationDataWithPreviouslyAssessedQuestion($previouslyAssessedQuestion);
                return;
            }
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
        $participantId = $this->students->get((int)$this->answerNavigationValue - 1);
        $answer = $this->answers->where('question_id', $question->id)
            ->where('test_participant_id', $participantId)
            ->first();

        return $answer && $question && $answer->question_id === $question->id;
    }

    private function currentIndexIsAnInfoQuestion()
    {
        return $this->questions->get((int)$this->questionNavigationValue - 1)?->first()->isType('Infoscreen');
    }

    private function figureOutAnswerScore(): ?int
    {
        $ratings = $this->currentAnswer->fresh()->answerRatings->whereNotNull('rating');
        if ($rating = $ratings->first(fn($rating) => $rating->type === AnswerRating::TYPE_SYSTEM)) {
            return $rating->rating;
        }
        if ($rating = $ratings->first(fn($rating) => $rating->type === AnswerRating::TYPE_STUDENT)) {
            return $rating->rating;
        }

        if (!$this->currentAnswer->isAnswered) {
            if ($ratings->where('type', AnswerRating::TYPE_TEACHER)->isEmpty()) {
                $this->updateOrCreateAnswerRating(0);
            }

            return 0;
        }

        return $ratings->first(fn($rating) => $rating->type === AnswerRating::TYPE_TEACHER)?->rating;
    }

    private function currentAnswerHasRatingsOfType(string $type): bool
    {
        return $this->currentAnswer->answerRatings->where('type', $type)->whereNotNull('rating')->isNotEmpty();
    }

    private function hasOnlyTwoFastScoringOptions(): bool
    {
        return $this->currentQuestion->score < 2 || $this->currentQuestion->all_or_nothing;
    }

    private function onFirstAnswerForQuestion(): bool
    {
        return (int)$this->answerNavigationValue === (int)$this->firstAnswerForQuestion;
    }

    private function onLastAnswerForQuestion(): bool
    {
        return (int)$this->answerNavigationValue === (int)$this->lastAnswerForQuestion;
    }

    private function onLastQuestionToAssess(): bool
    {
        return (int)$this->questionNavigationValue === $this->questions->count();
    }

    private function onFirstQuestionToAssess(): bool
    {
        return (int)$this->questionNavigationValue === 1;
    }

    /**
     * @param $value
     * @return void
     */
    private function updateOrCreateAnswerRating($value): void
    {
        $this->currentAnswer->answerRatings()
            ->updateOrCreate([
                'type'         => AnswerRating::TYPE_TEACHER,
                'test_take_id' => $this->testTakeData->id,
            ], [
                'rating' => $value
            ]);
    }

    private function getAssessedQuestionsCount(): int
    {
        $questionsIdsWithTimesRated = Answer::from('answers as a')
            ->distinct()
            ->selectRaw('questions.question_id')
            ->joinSub(function ($query) {
                $query->select('question_id')
                    ->from('answers')
                    ->join('test_participants as tp', 'tp.id', '=', 'answers.test_participant_id')
                    ->where('tp.test_take_id', $this->testTakeData->id)
                    ->distinct();
            }, 'questions', fn($join) => $join->on('questions.question_id', '=', 'a.question_id'))
            ->selectSub(function ($query) {
                $query->selectRaw('count(*)')
                    ->from('answer_ratings as ar')
                    ->whereRaw('ar.answer_id = a.id')
                    ->whereNull('ar.deleted_at');
            }, 'assessedCount')
            ->selectSub(function ($query) {
                $query->selectRaw('count(*)')
                    ->from('answers as a2')
                    ->whereRaw('a2.question_id = questions.question_id');
            }, 'answerCount')
            ->whereNull('a.deleted_at')
            ->withTrashed()
            ->get();

        return $questionsIdsWithTimesRated
            ->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType())
            ->where(fn($item) => $item->assessedCount >= $item->answerCount)
            ->count();
    }

    /**
     * @param $previouslyAssessedQuestion
     * @return void
     */
    private function setNavigationDataWithPreviouslyAssessedQuestion($previouslyAssessedQuestion): void
    {
        $this->questionNavigationValue = $this->questions->search($previouslyAssessedQuestion) + 1;
        $this->answerNavigationValue = $this->answers
                ->where('question_id', $previouslyAssessedQuestion->id)
                ->values()
                ->search(
                    $this->answers->where('question_id', $previouslyAssessedQuestion->id)->first()
                ) + 1;
    }

    private function getQuestionIdsForCurrentAssessmentType()
    {
        return $this->questions->discussionTypeFiltered($this->openOnly)->pluck('id');
    }

}