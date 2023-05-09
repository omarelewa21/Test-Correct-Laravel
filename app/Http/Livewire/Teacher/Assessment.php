<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Exceptions\AssessmentException;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Interfaces\CollapsableHeader;
use tcCore\Http\Livewire\EvaluationComponent;
use tcCore\Question;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;
use tcCore\View\Components\CompletionQuestionConvertedHtml;

class Assessment extends EvaluationComponent implements CollapsableHeader
{
    /*Template properties*/
    public bool $headerCollapsed = false;
    public array $assessmentContext = [
        'skipCoLearningNoDiscrepancies' => false,
        'skippedCoLearning'             => false,
        'assessedAt'                    => null,
        'assessmentType'                => null,
        'assessIndex'                   => null,
        'totalToAssess'                 => 0,
        'showStudentNames'              => false,
    ];

    protected bool $updatePage = false;

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
    public $questionCount;
    public $lastQuestionForStudent;
    public $firstQuestionForStudent;
    public $studentCount;
    public $firstAnswerForQuestion;
    public $lastAnswerForQuestion;

    /* Data properties filled from cache */
    protected $answerRatings;
    protected $students;

    /* Context properties */
    public int $progress = 0;
    public int $maxAssessedValue = 1;
    public bool $openOnly;
    public bool $isCoLearningScore = false;

    /* Lifecycle methods */
    protected function getListeners(): array
    {
        return parent::getListeners() + [
                'inline-feedback-saved' => 'handleFeedbackChange'
            ];
    }

    public function mount(TestTake $testTake): void
    {
        $this->testTakeUuid = $testTake->uuid;

        $this->headerCollapsed = Session::has("assessment-started-$this->testTakeUuid");
        $this->setData();

        $this->verifyTestTakeData();

        if ($this->headerCollapsed) {
            $this->skipBootedMethod();
            $this->start();
        }

        $this->setTemplateVariables();
    }

    public function booted(): void
    {
        if ($this->skipBooted) {
            return;
        }

        $this->setData();
        if ($this->headerCollapsed) {
            $this->hydrateCurrentProperties();
        }
    }

    public function render()
    {
        return view('livewire.teacher.assessment')->layout('layouts.assessment');
    }

    public function updatedScore($value)
    {
        $this->updateOrCreateAnswerRating(['rating' => $value]);
    }

    public function updatedFeedback($value)
    {
        $this->updateOrCreateAnswerFeedback($value);
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
            ]
        );
    }

    public function getShowAutomaticallyScoredToggleProperty(): bool
    {
        if ($this->currentQuestion->isType('Infoscreen')) {
            return false;
        }
        if (!$this->currentAnswer->isAnswered) {
            return false;
        }
        return $this->currentAnswerHasRatingsOfType(AnswerRating::TYPE_SYSTEM);
    }

    public function getAutomaticallyScoredValueProperty(): float
    {
        return $this->currentAnswerRatings()->where('type', AnswerRating::TYPE_SYSTEM)->first()->rating;
    }

    public function getCoLearningScoredValueProperty(): float
    {
        return $this->getCoLearningScoreForCurrentAnswer();
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
        return $this->currentQuestion->isDiscussionTypeOpen;
    }

    public function getShowScoreSliderProperty(): bool
    {
        return !$this->currentQuestion->isType('Infoscreen');
    }

    public function getDrawerScoringDisabledProperty(): bool
    {
        if (!$this->headerCollapsed) {
            return true;
        }

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
        }
        TestTake::whereUuid($this->testTakeUuid)->update($updates);

        $this->setData();
        $this->start($reset);

        $this->storeAssessmentSessionContext($args);

        return $this->headerCollapsed = true;
    }

    public function redirectBack()
    {
        Session::forget("assessment-started-$this->testTakeUuid");

        if ($this->referrer === 'cake' || blank($this->referrer)) {
            return CakeRedirectHelper::redirectToCake(
                routeName  : 'test_takes.view',
                uuid       : $this->testTakeUuid,
                returnRoute: '/teacher/test_takes/taken'
            );
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

        $answerForCurrentStudent = $answersForQuestion->first(
            fn($answer) => $answer->test_participant_id === $this->students->get($value - 1)
        );
        if (!$answerForCurrentStudent) {
            $answerForCurrentStudent = $this->retrieveAnswerBasedOnAction($action, $answersForQuestion);
            $value = $this->students->search($answerForCurrentStudent->test_participant_id) + 1;
        }

        $this->setComponentAnswerProperties($answerForCurrentStudent, $value);

        $this->lastAnswerForQuestion = $this->students->search($answersForQuestion->last()->test_participant_id) + 1;
        $this->firstAnswerForQuestion = $this->students->search($answersForQuestion->first()->test_participant_id) + 1;

        $this->currentAnswer->load('answerRatings');
        $this->score = $this->handleAnswerScore();
        $this->feedback = $this->getFeedbackForCurrentAnswer();
        $this->answerPanel = true;
        $this->setUserOnAnswer($this->currentAnswer);
        $this->setProgress();

        if (!$internal) {
            $this->dispatchUpdateQuestionNavigatorEvent();
        }

        return [
            'index' => $this->answerNavigationValue,
            'last'  => $this->lastAnswerForQuestion,
            'first' => $this->firstAnswerForQuestion,
        ];
    }

    /**
     * @throws AssessmentException
     */
    public function loadQuestion($position, $action = null): array
    {
        $newIndex = $position - 1;

        if ($nextQuestion = $this->questions->discussionTypeFiltered($this->openOnly)->get($newIndex)) {
            $hasAnswerForNextQuestion = $this->getAnswersForCurrentQuestion($nextQuestion)
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
                $this->loadQuestion(position: (int)$this->questionNavigationValue + 1, action: 'incr')
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
                $this->loadQuestion(position: (int)$this->questionNavigationValue - 1, action: 'decr')
            );
            return true;
        }

        throw new AssessmentException(
            'Oh my god you are amazing. no one should have been able to do this, but you did!'
        );
    }

    public function finalAnswerReached(): bool
    {
        return $this->onLastQuestionToAssess() && $this->onLastAnswerForQuestion();
    }

    public function onBeginningOfAssessment(): bool
    {
        return $this->onFirstQuestionToAssess() && $this->onFirstAnswerForQuestion();
    }

    public function toggleValueUpdated($id, $state): void
    {
        $json = $this->teacherRating()?->json ?? [];

        $json[$id] = $state === 'on';

        $this->updateOrCreateAnswerRating(['json' => $json]);
    }

    public function handleFeedbackChange()
    {
        $this->getFeedbackForCurrentAnswer();
    }

    public function deleteFeedback(): void
    {
        $this->currentAnswer->feedback()->where('user_id', auth()->id())->delete();
        $this->getFeedbackForCurrentAnswer();
    }


    /* Private methods */
    protected function setTemplateVariables(): void
    {
        $testTake = $this->testTakeData->refresh();
        $this->testName = $testTake->test->name ?? '';
        $this->openOnly = $testTake->assessment_type === 'OPEN_ONLY';

        $this->assessmentContext = [
            'skipCoLearningNoDiscrepancies' => (bool)($this->sessionSettings(
            )?->skipCoLearningNoDiscrepancies ?? $this->assessmentContext['skipCoLearningNoDiscrepancies']),
            'skippedCoLearning'             => $testTake->skipped_discussion,
            'assessedAt'                    => $this->getFormattedAssessedAtDate($testTake),
            'assessmentType'                => $testTake->assessment_type,
            'assessIndex'                   => $this->getAssessedQuestionsCount(),
            'totalToAssess'                 => $this->questions->discussionTypeFiltered($this->openOnly)->count(),
            'showStudentNames'              => $this->sessionSettings(
                )?->showStudentNames ?? $this->assessmentContext['showStudentNames'],
        ];

        $this->questionCount = $this->questions->count();
        $this->studentCount = $this->students->count();
        $this->updatePage = true;
    }

    /**
     * @return void
     */
    protected function setData(): void
    {
        $this->testTakeData = $this->getTestTakeData();

        $this->groups = $this->getGroups();

        $this->questions = $this->getQuestions();

        $this->answers = $this->getAnswers();

        $this->students = $this->getStudents();

        $this->maxAssessedValue = $this->testTakeData->fresh()->max_assessed_answer_index ?? 1;
    }

    protected function start(bool $reset = false): void
    {
        $this->openOnly = $this->testTakeData->fresh()->assessment_type === 'OPEN_ONLY';

        $this->initializeNavigationProperties($reset);

        $this->loadQuestion($this->questionNavigationValue);
    }

    private function skipBootedMethod(): void
    {
        $this->skipBooted = true;
    }

    /**
     * @return mixed
     */
    private function getAnswersForCurrentQuestion(?Question $question = null): Collection
    {
        $question ??= $this->currentQuestion;
        return $this->answers->where('question_id', $question->getKey())
            ->discrepancyFiltered((bool)$this->assessmentContext['skipCoLearningNoDiscrepancies'])
            ->values();
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
        $question = $this->getEdgeQuestionForStudent('last');

        return $this->getNavigationValueForQuestion($question);
    }

    /**
     * @return int
     */
    private function getFirstQuestionsCountForCurrentStudent(): int
    {
        $question = $this->getEdgeQuestionForStudent('first');

        return $this->getNavigationValueForQuestion($question);
    }

    private function setComponentQuestionProperties(Question $newQuestion, int $index): void
    {
        $this->currentQuestion = $newQuestion;
        $this->questionNavigationValue = $index + 1;
        $this->handleGroupQuestion();
    }

    private function setComponentAnswerProperties(Answer $answer, int $index): void
    {
        $this->currentAnswer = $answer;
        $this->answerNavigationValue = $index;
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
            throw new AssessmentException(
                sprintf(
                    'Cannot find closest question to navigate to based on the "%s" from question position %s',
                    $action,
                    $this->questionNavigationValue
                )
            );
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
            throw new AssessmentException(
                sprintf(
                    'Cannot find closest answer to navigate to based on "%s" from answer position %s',
                    $action,
                    $this->answerNavigationValue
                )
            );
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
        $answers = $answers->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType())
            ->discrepancyFiltered((bool)$this->assessmentContext['skipCoLearningNoDiscrepancies']);
        if ($action === 'last') {
            return $answers->last();
        }
        if ($action === 'first') {
            return $answers->first();
        }
        if ($action === 'decr') {
            return $answers->filter(fn($answer, $key) => $key < $currentIndex)->last();
        }
        if ($action === 'incr') {
            return $answers->filter(fn($answer, $key) => $key > $currentIndex)->first();
        }
        return null;
    }

    private function dispatchUpdateNavigatorEvent(string $navigator, array $updates): void
    {
        $this->updatePage = true;
        $this->dispatchBrowserEvent('update-navigation', ['navigator' => $navigator, 'updates' => $updates]);
        $this->dispatchBrowserEvent('update-scoring-data', $this->getScoringData());
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

    protected function initializeNavigationProperties(bool $reset = false): void
    {
        if ($this->validQueryStringPropertiesForNavigation()) {
            return;
        }

        if ($previousId = $this->testTakeData->fresh()->assessing_question_id) {
            $previouslyAssessedQuestion = $this->questions->discussionTypeFiltered($this->openOnly)
                ->where('id', $previousId)
                ->first();
            if ($previouslyAssessedQuestion) {
                $this->setNavigationDataWithPreviouslyAssessedQuestion($previouslyAssessedQuestion);
                return;
            }
        }

        $firstAnswer = $this->answers->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType())->first();
        if ($reset) {
            $firstAnswer = $this->getFirstAnswerWhichDoesntHaveATeacherOrSystemRating() ?? $this->answers->last();
        }

        $firstQuestionForAnswer = $this->questions->discussionTypeFiltered($this->openOnly)
            ->where('id', $firstAnswer->question_id)
            ->first();

        $this->questionNavigationValue = $this->getNavigationValueForQuestion($firstQuestionForAnswer);
        $this->answerNavigationValue = $this->students->search($firstAnswer->test_participant_id) + 1;
    }

    private function validQueryStringPropertiesForNavigation(): bool
    {
        if (blank($this->questionNavigationValue) || blank($this->answerNavigationValue)) {
            return false;
        }

        $question = $this->questions->discussionTypeFiltered($this->openOnly)->get(
            (int)$this->questionNavigationValue - 1
        );
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

    protected function handleAnswerScore(): null|int|float
    {
        $ratings = $this->currentAnswerRatings()->whereNotNull('rating');
        $this->isCoLearningScore = false;
        if (!$this->currentAnswer->isAnswered) {
            if ($ratings->where('type', AnswerRating::TYPE_TEACHER)->isEmpty()) {
                $this->updateOrCreateAnswerRating(['rating' => 0]);
            }
            return 0;
        }

        if ($rating = $ratings->first(fn($rating) => $rating->type === AnswerRating::TYPE_TEACHER)) {
            return $rating->rating;
        }

        if ($rating = $ratings->first(fn($rating) => $rating->type === AnswerRating::TYPE_SYSTEM)) {
            return $rating->rating;
        }

        if ($ratings->where('type', AnswerRating::TYPE_STUDENT)->isNotEmpty()) {
            $this->isCoLearningScore = true;
            return $this->getCoLearningScoreForCurrentAnswer();
        }

        return null;
    }

    private function currentAnswerHasRatingsOfType(string $type): bool
    {
        return $this->currentAnswer
            ->answerRatings
            ->where('type', $type)
            ->whereNotNull('rating')
            ->isNotEmpty();
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
        return (int)$this->questionNavigationValue === $this->getNavigationValueForQuestion(
                $this->questions->discussionTypeFiltered($this->openOnly)->last()
            );
    }

    private function onFirstQuestionToAssess(): bool
    {
        return (int)$this->questionNavigationValue === $this->getNavigationValueForQuestion(
                $this->questions->discussionTypeFiltered($this->openOnly)->first()
            );
    }

    /**
     * @param $value
     * @return void
     */
    private function updateOrCreateAnswerRating(array $updates): void
    {
        $this->currentAnswer->answerRatings()
            ->updateOrCreate([
                'type'         => AnswerRating::TYPE_TEACHER,
                'test_take_id' => $this->testTakeData->id,
                'user_id'      => auth()->id(),
            ], $updates);
    }

    private function getAssessedQuestionsCount(): int
    {
        $unansweredQuestionCount = $this->answers
            ->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType())
            ->where(fn($answer) => $answer->answerRatings->where('type', '!=', AnswerRating::TYPE_STUDENT)->isEmpty())
            ->pluck('question_id')
            ->unique()
            ->count();

        return $this->questions->discussionTypeFiltered($this->openOnly)->count() - $unansweredQuestionCount;
    }

    /**
     * @param $previouslyAssessedQuestion
     * @return void
     */
    private function setNavigationDataWithPreviouslyAssessedQuestion($previouslyAssessedQuestion): void
    {
        $this->questionNavigationValue = $this->getNavigationValueForQuestion($previouslyAssessedQuestion);
        $this->answerNavigationValue = $this->students->search(
                $this->answers->where('question_id', $previouslyAssessedQuestion->id)->first()->test_participant_id
            ) + 1;
    }

    private function getQuestionIdsForCurrentAssessmentType()
    {
        return $this->questions->discussionTypeFiltered($this->openOnly)->pluck('id');
    }

    protected function currentAnswerCoLearningRatingsHasNoDiscrepancy(?Answer $answer = null): bool
    {
        $answer ??= $this->currentAnswer;
        return $answer->answerRatings->where('type', AnswerRating::TYPE_STUDENT)
                ->keyBy('rating')
                ->count() === 1;
    }

    public function coLearningRatings(): Collection
    {
        return parent::coLearningRatings()->sortBy('user.name_first');
    }

    private function sessionSettings(): object
    {
        return (object)Session::get('assessment-started-' . $this->testTakeUuid, []);
    }

    private function updateOrCreateAnswerFeedback($value)
    {
        $this->currentAnswer->feedback()
            ->updateOrCreate(
                ['user_id' => auth()->id()],
                ['message' => clean($value)]
            );
    }

    private function getFeedbackForCurrentAnswer(): string
    {
        $feedback = $this->currentAnswer
            ->feedback()
            ->where('user_id', auth()->id())
            ->first()
            ?->message ?? '';

        $this->hasFeedback = filled($feedback);
        return $this->currentQuestion->isSubType('writing') ? '' : $feedback;
    }

    private function setProgress(): void
    {
        [$percentagePerAnswer, $assessedAnswers] = $this->getProgressPropertiesForCalculation();

        $currentAnswerIndexOfAllAnswers = $this->answers->search(function ($answer) {
            return $answer->id === $this->currentAnswer->id;
        });

        $maxAssessedValue = max($currentAnswerIndexOfAllAnswers, $this->maxAssessedValue);

        if ($maxAssessedValue > $this->maxAssessedValue) {
            $this->updateMaxAssessedValue($maxAssessedValue);
        }

        $multiplier = $assessedAnswers->filter(fn($item, $key) => $key <= $maxAssessedValue)->count();

        $newPercentage = floor($percentagePerAnswer * $multiplier);

        if ($newPercentage > $this->progress) {
            $this->progress = $newPercentage;
        }
    }

    private function getCoLearningScoreForCurrentAnswer(): float|int
    {
        $rating = $this->currentAnswer
            ->answerRatings
            ->filter(fn($rating) => $rating->type === AnswerRating::TYPE_STUDENT)
            ->median('rating');

        return $this->currentQuestion->decimal_score ? floor(($rating * 2) / 2) : (int)round($rating);
    }

    /**
     * @return array
     */
    private function getProgressPropertiesForCalculation(): array
    {
        $filteredAnswers = $this->answers
            ->discrepancyFiltered((bool)$this->assessmentContext['skipCoLearningNoDiscrepancies']);

        $percentagePerAnswer = 1 / $filteredAnswers->count() * 100;

        $assessedAnswers = $filteredAnswers->where(function ($answer) {
            if ($answer->answerRatings->where('type', '!=', AnswerRating::TYPE_STUDENT)->isNotEmpty()) {
                return true;
            }
            return $answer->hasDiscrepancy === false;
        });

        return [$percentagePerAnswer, $assessedAnswers];
    }

    private function getNavigationValueForQuestion(Question $question): int
    {
        return $this->questions->search(fn($q) => $q->id === $question->id) + 1;
    }

    private function getEdgeQuestionForStudent(string $edge): Question
    {
        if (!in_array($edge, ['first', 'last'])) {
            throw new AssessmentException(
                sprintf(
                    'Something went wrong with getting the "%s" answer from all available. Did you mean "first" or "last"?',
                    $edge
                )
            );
        }
        return $this->questions->where(
            'id',
            $this->getAvailableAnswersForCurrentStudent()
                ->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType())
                ->$edge()
                ->question_id
        )->first();
    }

    private function verifyTestTakeData()
    {
        if (filled($this->answers)) {
            return true;
        }

        return CakeRedirectHelper::redirectToCake(
            routeName   : 'test_takes.view',
            uuid        : $this->testTakeUuid,
            returnRoute : '/teacher/test_takes/taken',
            notification: ['message' => __('assessment.no_answers'), 'type' => 'error']
        );
    }

    private function updateMaxAssessedValue($currentAnswerIndexOfAllAnswers)
    {
        TestTake::whereUuid($this->testTakeUuid)
            ->update([
                'max_assessed_answer_index' => $currentAnswerIndexOfAllAnswers
            ]);
    }

    private function getFirstAnswerWhichDoesntHaveATeacherOrSystemRating(): ?Answer
    {
        return $this->answers->first(function ($answer) {
            return $answer->answerRatings->doesntContain(function ($rating) {
                return $rating->type === AnswerRating::TYPE_TEACHER || $rating->type === AnswerRating::TYPE_SYSTEM;
            });
        });
    }

    /**
     * @param $args
     * @return void
     */
    private function storeAssessmentSessionContext($args): void
    {
        $contextData = [
            'skipCoLearningNoDiscrepancies' => (bool)$this->assessmentContext['skipCoLearningNoDiscrepancies'],
            'showStudentNames'              => (bool)$this->assessmentContext['showStudentNames'],
        ];

        Session::put(
            "assessment-started-$this->testTakeUuid",
            $args + $contextData
        );
    }

    private function getFormattedAssessedAtDate(TestTake $testTake)
    {
        return filled($testTake->assessed_at)
            ? str($testTake->assessed_at->translatedFormat('j M Y'))->replace('.', '')
            : null;
    }

    protected function hydrateCurrentProperties(): void
    {
        $this->setUserOnAnswer($this->currentAnswer);
        $this->currentQuestion = $this->questions->get((int)$this->questionNavigationValue - 1);
    }

    private function setUserOnAnswer(Answer $answer): void
    {
        $answer->user = $this->testTakeData
            ->testParticipants
            ->find($answer->test_participant_id)
            ?->user ?? User::getDeletedNewUser();

        $answer->user->shortLastname = str(
            sprintf(
                '%s %s',
                $answer->user->name_suffix,
                substr($answer->user->name, 0, 1)
            )
        )
            ->squish()
            ->append('.')
            ->value();
    }

    protected function getTestTakeData(): TestTake
    {
        return cache()->remember("assessment-data-$this->testTakeUuid", now()->addDays(3), function () {
            return TestTake::whereUuid($this->testTakeUuid)
                ->with([
                    'testParticipants:id,uuid,test_take_id,user_id,test_take_status_id',
                    'testParticipants.answers:id,uuid,test_participant_id,question_id,json,order,final_rating,done',
                    'testParticipants.answers.answerRatings:id,answer_id,type,rating,advise,user_id',
                    'testParticipants.answers.answerRatings.user:id,name,name_first,name_suffix',
                    'test:id,name',
                    'test.testQuestions:id,test_id,question_id',
                    'test.testQuestions.question',
                ])
                ->first();
        });
    }

    protected function getAnswers(): Collection
    {
        return $this->testTakeData->testParticipants
            ->load([
                'answers:id,uuid,test_participant_id,question_id,json,order,final_rating,done',
                'answers.answerRatings:id,answer_id,type,rating,advise,user_id',
                'answers.answerRatings.user:id,name,name_first,name_suffix',
            ])
            ->flatMap(function ($participant) {
                return $participant->answers->map(function ($answer) {
                    $coLearningRatings = $answer->answerRatings->where('type', AnswerRating::TYPE_STUDENT);
                    $answer->hasDiscrepancy = $coLearningRatings
                        ? !$this->currentAnswerCoLearningRatingsHasNoDiscrepancy($answer)
                        : null;

                    $answer->sortOrder = $this->questions->search(fn($q) => $q->id === $answer->question_id) + 1;
                    return $answer;
                });
            })
            ->sortBy(['sortOrder', 'test_participant_id'])
            ->values();
    }

    private function getStudents(): Collection
    {
        return $this->testTakeData
            ->testParticipants
            ->where('test_take_status_id', '>', TestTakeStatus::STATUS_TAKING_TEST)
            ->sortBy('id')
            ->pluck('id');
    }

    protected function getQuestions(): Collection
    {
        return $this->testTakeData
            ->test
            ->getFlatQuestionList(
                function ($relationModel) {
                    $relationModel->question->isDiscussionTypeOpen = !$relationModel->question->canCheckAnswer();
                }
            );
    }

    protected function getGroups(): Collection
    {
        return $this->testTakeData->test->testQuestions
            ->map(fn($testQuestion) => $testQuestion->question->isType('Group') ? $testQuestion->question : null)
            ->filter();
    }

    public function getHasNoOpenQuestionProperty(): bool
    {
        return !$this->testTakeData->test->hasOpenQuestion();
    }

    public function getScoringData(): array
    {
        return [
            'initialScore'          => $this->score,
            'maxScore'              => $this->currentQuestion?->score,
            'halfPoints'            => (bool)$this->currentQuestion?->decimal_score,
            'drawerScoringDisabled' => $this->drawerScoringDisabled,
            'pageUpdated'           => $this->updatePage,
            'isCoLearningScore'     => $this->isCoLearningScore,
        ];
    }

    public function getDisplayableCompletionQuestionText()
    {
        return Blade::renderComponent(new CompletionQuestionConvertedHtml($this->currentQuestion, $context='assessment'));
    }
}
