<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Exceptions\AssessmentException;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Interfaces\CollapsableHeader;
use tcCore\Http\Livewire\EvaluationComponent;
use tcCore\Question;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;
use tcCore\UserFeatureSetting;
use tcCore\View\Components\CompletionQuestionConvertedHtml;

class Assessment extends EvaluationComponent implements CollapsableHeader
{
    /*Template properties*/
    public bool $headerCollapsed = false;
    public array $assessmentContext = [
        'assessment_skip_no_discrepancy_answer' => false,
        'skippedCoLearning'                     => false,
        'assessedAt'                            => null,
        'assessmentType'                        => null,
        'assessIndex'                           => null,
        'totalToAssess'                         => 0,
        'assessment_show_student_names'         => false,
    ];

    protected bool $updatePage = false;

    /*Query string properties*/
    protected $queryString = [
        'referrer'                => ['except' => '', 'as' => 'r'],
        'questionNavigationValue' => ['except' => '', 'as' => 'qi'],
        'answerNavigationValue'   => ['except' => '', 'as' => 'ai'],
        'participant'             => ['except' => ''],
    ];
    public string $referrer = '';
    public string $questionNavigationValue = '';
    public string $answerNavigationValue = '';
    public string $participant = '';

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
    public int $maxAssessedValue = 1;
    public bool $openOnly;
    public bool $isCoLearningScore = false;
    public bool $skipNoDiscrepancies = false;
    public bool $webSpellCheckerEnabled;
    public $answerFeedback;
    public bool $scoreWarningDispatchedForQuestion;
    public bool $showNewAssessmentNotification = false;

    public bool $singleParticipantState = false;
    protected ?TestParticipant $testParticipant;
    public ?int $participantPosition;

    /* Lifecycle methods */
    protected function getListeners(): array
    {
        return parent::getListeners() + [
                'inline-feedback-saved' => 'handleFeedbackChange'
            ];
    }

    public function mount(TestTake $testTake): void
    {
        Gate::authorize('isAllowedToViewTestTake',[$testTake, false, false ]);

        $this->testTakeUuid = $testTake->uuid;
        $this->headerCollapsed = Session::has("assessment-started-$this->testTakeUuid");

        if ($this->participant) {
            $this->singleParticipantState = true;
            $this->testParticipant = TestParticipant::whereUuid($this->participant)->firstOrFail();
            $this->participantPosition = $testTake->testParticipants()
                    ->where('id', '<=', $this->testParticipant->id)
                    ->count();
        }

        $this->setData();

        $this->verifyTestTakeData();

        if ($this->headerCollapsed) {
            $this->skipBootedMethod();
            $this->start();
        }
        if (!$this->headerCollapsed) {
            if ($this->participant) {
                $this->headerCollapsed = true;
                $this->skipBootedMethod();
                $this->start();
                $this->storeAssessmentSessionContext(['assessment_type' => 'ALL']);
            }
        }

        $this->setTemplateVariables();

        $this->createAnswerRatingsForUnansweredQuestions();

        $this->setWebspellcheckerEnabled();
    }

    public function booted(): void
    {
        if ($this->skipBooted) {
            return;
        }

        if ($this->participant) {
            $this->testParticipant = TestParticipant::whereUuid($this->participant)->firstOrFail();
        }

        $this->setData();
        if ($this->headerCollapsed) {
            $this->hydrateCurrentProperties();
        }
    }

    public function render()
    {
        $this->setNecklaceNavigationDataOnQuestion();
        return view('livewire.teacher.assessment')->layout('layouts.assessment');
    }

    public function updatedScore($value): void
    {
        $this->updateOrCreateAnswerRating(['rating' => $value]);
        $this->dispatchScoreNotificationForQuestion();
    }

    public function updatedFeedback($value)
    {
        $this->updateOrCreateAnswerFeedback($value);
    }

    public function updatedAssessmentContext($value, $name): void
    {
        if ($name === 'assessment_skip_no_discrepancy_answer') {
            $this->skipNoDiscrepancies = (bool)$value;
        }
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

    public function getCoLearningScoredValueProperty(): false|float
    {
        if($this->currentAnswer->hasCoLearningDiscrepancy() === false) {
            return $this->currentAnswer->getStudentAnswerRatings()->first()->rating;
        }
        return false;
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
            $updates['assessing_answer_index'] = null;
        }
        TestTake::whereUuid($this->testTakeUuid)->update($updates);

        $this->setData();

        $this->headerCollapsed = true;

        $this->start($reset);

        $this->storeAssessmentSessionContext($args);

        return true;
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
            $value = $this->getAnswerIndex($answerForCurrentStudent);
        }

        $this->setComponentAnswerProperties($answerForCurrentStudent, $value);

        $this->lastAnswerForQuestion = $this->getAnswerIndex($answersForQuestion->last());
        $this->firstAnswerForQuestion = $this->getAnswerIndex($answersForQuestion->first());

        $this->score = $this->handleAnswerScore();
        $this->determineHasFeedbackForCurrentAnswer();
        $this->answerPanel = true;
        $this->setUserOnAnswer($this->currentAnswer);

        $this->dispatchUpdateQuestionNavigatorEvent();

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
            return $this->loadAnyNextAnswerWithAction('incr');
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
            return $this->loadAnyNextAnswerWithAction('decr');
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
        if (in_array(needle: $id, haystack: [null, ""])) {
            return;
        }

        $json = $this->teacherRating()?->json ?? [];

        $json[$id] = $state === 'on';

        $this->updateOrCreateAnswerRating(['json' => $json]);
    }

    public function handleFeedbackChange()
    {
        $this->determineHasFeedbackForCurrentAnswer();
    }

    public function deleteFeedback(): void
    {
        $this->currentAnswer->feedback()->where('user_id', auth()->id())->delete();
        $this->determineHasFeedbackForCurrentAnswer(false);
    }

    public function removeNotification(): void
    {
        UserFeatureSetting::setSetting(
            auth()->user(),
            UserFeatureSettingEnum::SEEN_ASSESSMENT_NOTIFICATION,
            true
        );
        $this->showNewAssessmentNotification = false;
    }

    public function finish()
    {
        if ($this->testTakeData->test_take_status_id < TestTakeStatus::STATUS_DISCUSSED) {
            TestTake::whereUuid($this->testTakeUuid)
                ->update(['test_take_status_id' => TestTakeStatus::STATUS_DISCUSSED]);
        }

        return $this->redirectBack();
    }

    /* Private methods */
    protected function setTemplateVariables(): void
    {
        $testTake = $this->testTakeData->refresh();
        $this->testName = $testTake->test->name ?? '';
        $this->openOnly = $testTake->assessment_type === 'OPEN_ONLY';

        $this->assessmentContext = [
            'assessment_skip_no_discrepancy_answer' => $this->getSkipDiscrepancyValue($testTake),
            'skippedCoLearning'                     => $testTake->skipped_discussion,
            'assessedAt'                            => $this->getFormattedAssessedAtDate($testTake),
            'assessmentType'                        => $testTake->assessment_type,
            'assessIndex'                           => $this->getAssessedQuestionsCount(),
            'totalToAssess'                         => $this->questions->discussionTypeFiltered($this->openOnly)->count(
            ),
            'assessment_show_student_names'         => $this->getSessionSettingValue('assessment_show_student_names'),
        ];

        $this->skipNoDiscrepancies = (bool)$this->assessmentContext['assessment_skip_no_discrepancy_answer'];
        $this->questionCount = $this->questions->count();
        $this->studentCount = $this->students->count();
        $this->updatePage = true;
        $this->showNewAssessmentNotification = !UserFeatureSetting::getSetting(
            user : auth()->user(),
            title: UserFeatureSettingEnum::SEEN_ASSESSMENT_NOTIFICATION,
            default: false,
        );
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
        return $this->answersWithDiscrepancyFilter()
            ->where('question_id', $question->getKey())
            ->values();
    }

    /**
     * @return mixed
     */
    private function getAvailableAnswersForCurrentStudent(): Collection
    {
        return $this->answersWithDiscrepancyFilter()
            ->where('test_participant_id', $this->currentAnswer->test_participant_id)
            ->values();
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
        $this->scoreWarningDispatchedForQuestion = false;
    }

    private function setComponentAnswerProperties(Answer $answer, int $index): void
    {
        $this->currentAnswer = $answer;
        $this->currentAnswer->load('answerRatings');
        $this->answerNavigationValue = $index;
        TestTake::whereUuid($this->testTakeUuid)->update(['assessing_answer_index' => $index]);

        $this->getSortedAnswerFeedback();
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
        return $this->students->get((int)$this->answerNavigationValue - 1);
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
        $answers = $this->answersWithDiscrepancyFilter($answers)
            ->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType());
        return match ($action) {
            'last' => $answers->last(),
            'first' => $answers->first(),
            'decr' => $answers->filter(fn($answer, $key) => $key < $currentIndex)->last(),
            'incr' => $answers->filter(fn($answer, $key) => $key > $currentIndex)->first(),
            default => null,
        };
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

    private function dispatchUpdateQuestionNavigatorEvent(array|null $questionUpdates = null): void
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

        if (!$this->openOnly && !$this->singleParticipantState) {
            $previousId = $this->testTakeData->fresh()->assessing_question_id;

            if (!$previousId) {
                $this->setNavigationDataToStartPosition();
                return;
            }

            $previouslyAssessedQuestion = $this->questions
                ->discussionTypeFiltered($this->openOnly)
                ->whereIn('id', $this->answersWithDiscrepancyFilter()->pluck('question_id'))
                ->where('id', $previousId)
                ->first();

            if (!$previouslyAssessedQuestion) {
                $this->setNavigationDataToStartPosition();
                return;
            }

            $previousAnswerIndex = $this->getPreviouslyAssessedAnswerIndex($previouslyAssessedQuestion);

            $firstUnscoredAnswer = $this->getFirstAnswerWhichDoesntHaveATeacherOrSystemRating();
            if ($this->previouslyAssessedQuestionHasUnscoredOpenQuestionAnswerBeforeIt(
                $previouslyAssessedQuestion,
                $firstUnscoredAnswer,
                $previousAnswerIndex
            )) {
                $this->setNavigationDataWithFirstUnscoredAnswer($firstUnscoredAnswer);
                return;
            }

            $this->setNavigationDataWithPreviouslyAssessedQuestion($previouslyAssessedQuestion, $previousAnswerIndex);
            return;
        }

        $firstAnswer = $this->getFirstAnswerWhichDoesntHaveATeacherOrSystemRating()
            ?? $this->answersWithDiscrepancyFilter()
                ->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType())
                ->last();
        $this->setNavigationDataWithFirstUnscoredAnswer($firstAnswer);
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
        $answer = $this->answersWithDiscrepancyFilter()
            ->where('question_id', $question->id)
            ->where('test_participant_id', $participantId)
            ->first();

        return $answer && $question && $answer->question_id === $question->id;
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
        $this->isCoLearningScore = $this->getCurrentAnswer()->answerRatings->whereIn('type', [AnswerRating::TYPE_TEACHER, AnswerRating::TYPE_SYSTEM])->isEmpty();

        return $this->getCurrentAnswer()->calculateFinalRating();
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
        $lastPossibleQuestion = $this->answersWithDiscrepancyFilter()
                ->whereIn('question_id', $this->getQuestionIdsForCurrentAssessmentType())
                ->last()
                ?->question_id === $this->currentQuestion->id;

        return $lastPossibleQuestion || (int)$this->questionNavigationValue === $this->getNavigationValueForQuestion(
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

    /** @TODO @roan should this be moved towards the TestTakeHelper and merged with the method there? */
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

    private function setNavigationDataWithPreviouslyAssessedQuestion($previouslyAssessedQuestion, $answerIndex): void
    {
        $this->questionNavigationValue = $this->getNavigationValueForQuestion($previouslyAssessedQuestion);

        $this->answerNavigationValue = $answerIndex ?? $this->getAnswerIndex(
            $this->answersWithDiscrepancyFilter()
                ->where('question_id', $previouslyAssessedQuestion->id)
                ->first()
        );
    }

    private function getQuestionIdsForCurrentAssessmentType()
    {
        return $this->questions->discussionTypeFiltered($this->openOnly)->pluck('id');
    }

    protected function currentAnswerCoLearningRatingsHasNoDiscrepancy(?Answer $answer = null): bool
    {
        $answer ??= $this->currentAnswer;
        return !$answer->hasCoLearningDiscrepancy();
    }

    protected function currentAnswerHasToggleDiscrepanciesInCoLearningRatings(?Answer $answer = null): bool
    {
        $answer ??= $this->currentAnswer;
        return !!$answer->discrepancyInToggleData;
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

    private function determineHasFeedbackForCurrentAnswer($value = null): void
    {
        if(is_bool($value)){
            $this->hasFeedback = $value;
            return;
        }
        $this->hasFeedback = $this->currentAnswer
            ->feedback()
            ->exists();
    }

    public function assessedAllAnswers(): bool
    {
        if (!$this->finalAnswerReached()) {
            return false;
        }
        $filteredAnswers = $this->answersWithDiscrepancyFilter();
        $assessedAnswerCount = $filteredAnswers->where(function ($answer) {
            $answer->load('answerRatings');
            if ($answer->answerRatings->fresh()->where('type', '!=', AnswerRating::TYPE_STUDENT)->isNotEmpty()) {
                return true;
            }
            return $answer->hasDiscrepancy === false;
        })->count();

        return $filteredAnswers->count() === $assessedAnswerCount;
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
        return $this->answersWithDiscrepancyFilter()
            ->filter(function ($answer) {
                $needsAnswerRating = $this->questions->first(fn($q) => $q->id === $answer->question_id)?->isDiscussionTypeOpen;
                $hasNoAnswerRating = $answer->answerRatings->doesntContain(function ($rating) {
                    return $rating->type === AnswerRating::TYPE_TEACHER || $rating->type === AnswerRating::TYPE_SYSTEM;
                });
                return $needsAnswerRating && $hasNoAnswerRating;
            })
            ->first();
    }

    /**
     * @param $args
     * @return void
     */
    private function storeAssessmentSessionContext($args): void
    {
        $contextData = [
            'assessment_skip_no_discrepancy_answer' => (bool)$this->assessmentContext['assessment_skip_no_discrepancy_answer'],
            'assessment_show_student_names'         => (bool)$this->assessmentContext['assessment_show_student_names'],
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
        $cacheKey = "assessment-data-$this->testTakeUuid";
        if ($this->singleParticipantState) {
            $cacheKey = sprintf("assessment-data-%s-%s", $this->testTakeUuid, $this->testParticipant->uuid);
        }
        return cache()->remember($cacheKey, now()->addDays(3), function () {
            return TestTake::whereUuid($this->testTakeUuid)
                ->with([
                    'testParticipants' => function ($query) {
                        return $query->when(
                            $this->singleParticipantState,
                            fn($query) => $query->where('id', $this->testParticipant->getKey())
                        )
                            ->select('id', 'uuid', 'test_take_id', 'user_id', 'test_take_status_id');
                    }
                    ,
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
                'answers:id,uuid,test_participant_id,question_id,json,order,final_rating,done,commented_answer',
                'answers.answerRatings:id,answer_id,type,rating,advise,user_id',
                'answers.answerRatings.user:id,name,name_first,name_suffix',
            ])
            ->flatMap(function ($participant) {
                return $participant->answers->map(function ($answer) {
                    $answer->hasDiscrepancy = $answer->hasCoLearningDiscrepancy();

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

    public function getHasNoOpenQuestionProperty(): bool
    {
        return !$this->testTakeData->test->hasOpenQuestion()
            || $this->answersWithDiscrepancyFilter(
                $this->answers->whereIn(
                    'question_id',
                    $this->questions->discussionTypeFiltered(true)->pluck('id')
                )->reject(fn($answer) => !$answer->isAnswered)
            )->isEmpty();
    }

    public function canUseDiscrepancyToggle()
    {
        return $this->questions
            ->where('isDiscussionTypeOpen')
            ->where(function ($question) {
                return $this->answers
                    ->where('question_id', $question->id)
                    ->filter(function ($answer) {
                        return $answer->hasDiscrepancy === false;
                    })
                    ->isEmpty();
            })
            ->isNotEmpty();
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

    private function getSessionSettingValue($setting): bool
    {
        if (property_exists($this->sessionSettings(), $setting)) {
            return $this->sessionSettings()?->$setting;
        }

        return UserFeatureSetting::getSetting(
            user   : auth()->user(),
            title  : UserFeatureSettingEnum::tryFrom($setting),
            default: $this->assessmentContext[$setting]
        );
    }

    public function getDisplayableCompletionQuestionText()
    {
        return Blade::renderComponent(
            new CompletionQuestionConvertedHtml($this->currentQuestion, $context = 'assessment')
        );
    }

    private function getSkipDiscrepancyValue(TestTake $testTake)
    {
        if ($testTake->skipped_discussion) {
            return false;
        }

        if ($this->canUseDiscrepancyToggle()) {
            return $this->getSessionSettingValue('assessment_skip_no_discrepancy_answer');
        }
        return false;
    }

    private function getDefaultAnswerIndexForPreviouslyAssessedQuestion($previouslyAssessedQuestion): int
    {
        return $this->getAnswerIndex(
            $this->getAnswersForCurrentQuestion($previouslyAssessedQuestion)->first()
        );
    }

    /**
     * @param $previouslyAssessedQuestion
     * @return bool
     */
    private function previouslyAssessedQuestionHasUnscoredOpenQuestionAnswerBeforeIt(
        $previouslyAssessedQuestion,
        $firstUnscoredAnswer,
        $previousAnswerIndex
    ): bool {
        if (!$firstUnscoredAnswer) {
            return false;
        }

        $previouslyAssessedQuestionPosition = $this->questions
            ->discussionTypeFiltered($this->openOnly)
            ->search(fn($question) => $question->id === $previouslyAssessedQuestion->id);

        $firstUnscoredAnswerQuestionPosition = $this->questions
            ->discussionTypeFiltered($this->openOnly)
            ->search(fn($question) => $question->id === $this->questions
                    ->where('id', $firstUnscoredAnswer->question_id)
                    ->first()->id
            );
        if ($firstUnscoredAnswerQuestionPosition === $previouslyAssessedQuestionPosition) {
            return $this->getAnswerIndex($firstUnscoredAnswer) < $previousAnswerIndex;
        }

        return $firstUnscoredAnswerQuestionPosition < $previouslyAssessedQuestionPosition;
    }

    private function setNavigationDataWithFirstUnscoredAnswer(Answer $firstAnswer)
    {
        $firstQuestionForAnswer = $this->getQuestionForAnswer($firstAnswer);

        $this->questionNavigationValue = $this->getNavigationValueForQuestion($firstQuestionForAnswer);
        $this->answerNavigationValue = $this->getAnswerIndex($firstAnswer);
    }

    /**
     * @return void
     */
    private function setNavigationDataToStartPosition(): void
    {
        $firstAnswer = $this->answersWithDiscrepancyFilter()->first();

        $firstQuestionForAnswer = $this->getQuestionForAnswer($firstAnswer);

        $this->questionNavigationValue = $this->getNavigationValueForQuestion($firstQuestionForAnswer);
        $this->answerNavigationValue = $this->getAnswerIndex($firstAnswer);
    }

    private function getQuestionForAnswer(Answer $answer): ?Question
    {
        return $this->questions
            ->discussionTypeFiltered($this->openOnly)
            ->first(fn($question) => $question->id === $answer->question_id);
    }

    /**
     * @param $previouslyAssessedQuestion
     * @return int
     */
    private function getPreviouslyAssessedAnswerIndex($previouslyAssessedQuestion): int
    {
        $previousAnswerIndex = $this->testTakeData->fresh()->assessing_answer_index;

        return $previousAnswerIndex ?? $this->getDefaultAnswerIndexForPreviouslyAssessedQuestion(
            $previouslyAssessedQuestion
        );
    }

    /**
     * @param Answer $answer
     * @return int
     */
    private function getAnswerIndex(Answer $answer): int
    {
        return $this->students->search($answer->test_participant_id) + 1;
    }

    private function getSkipNoDiscrepanciesValue(): bool
    {
        return $this->skipBooted
            ? $this->getSessionSettingValue('assessment_skip_no_discrepancy_answer')
            : $this->skipNoDiscrepancies;
    }

    public function getDiscrepancyTranslationKey(): string
    {
        if($this->studentRatings()->whereNotNull('rating')->count() === 1) {
            return 'assessment.scored_by_one_student';
        }

        if ($this->currentAnswerCoLearningRatingsHasNoDiscrepancy()) {
            return 'assessment.no_discrepancy';
        }

        return 'assessment.discrepancy';
    }

    private function answersWithDiscrepancyFilter(Collection $answers = null): Collection
    {
        $answers ??= $this->answers;
        return $answers->discrepancyFiltered($this->getSkipNoDiscrepanciesValue())
            ->when($this->openOnly, function ($answers) {
                return $answers->reject(fn($answer) => !$answer->isAnswered);
            });
    }

    private function createAnswerRatingsForUnansweredQuestions()
    {
        $this->answers->where(fn($answer) => $answer->isAnswered === false)
            ->each(function ($answer) {
                $answer->answerRatings()
                    ->firstOrcreate([
                        'type'         => AnswerRating::TYPE_TEACHER,
                        'test_take_id' => $this->testTakeData->id,
                        'user_id'      => auth()->id(),
                        'rating'       => 0,
                    ]);
            });
    }

    private function setNecklaceNavigationDataOnQuestion(): void
    {
        $enabledQuestionIds = $this->getQuestionIdsForCurrentAssessmentType();
        $this->questions->each(function ($question) use ($enabledQuestionIds) {
            $answers = $this->answersWithDiscrepancyFilter()->where('question_id', $question->id);
            $question->navEnabled = $enabledQuestionIds->contains($question->id) && $answers->where('question_id', $question->id)->isNotEmpty();

            if (!$question->isDiscussionTypeOpen) {
                $question->doneAssessing = true;
                return;
            }
            $question->doneAssessing = $answers
                ->where(function ($answer) {
                    return $answer->hasDiscrepancy === false
                        ? false
                        : $answer->teacherRatings()->whereNotNull('rating')->isEmpty();
                })
                ->isEmpty();
        });

        $this->addGroupConnectorPropertyToQuestionIfNecessary();
    }

    public function getTitleTagForNavigation(Question $question): string
    {
        if ($this->openOnly && !$question->isDiscussionTypeOpen) {
            return __('assessment.disabled_nav_closed_question');
        }
        return __('assessment.disabled_nav_no_answer');
    }

    private function addGroupConnectorPropertyToQuestionIfNecessary(): void
    {
        $this->questions
            ->whereNotNull('belongs_to_groupquestion_id')
            ->groupBy('belongs_to_groupquestion_id')
            ->each(fn($group) => $group->pop())
            ->flatten()
            ->each(fn($question) => $question->connector = true);
    }

    private function loadAnyNextAnswerWithAction(string $action): bool
    {
        $currentAnswerIndex = $this->answers->search(fn($answer) => $answer->id === $this->currentAnswer->id);
        $newAnswer = $this->getClosestAvailableAnswer($action, $this->answers, $currentAnswerIndex);
        if (!$newAnswer) {
            \Bugsnag::notifyException(
                new AssessmentException(
                    sprintf('Trying to use the \'%s\' button , but there\'s no next answer available.', $action)
                )
            );
            return true;
        }
        $this->setComponentAnswerProperties($newAnswer, $this->getAnswerIndex($newAnswer));

        $this->dispatchUpdateQuestionNavigatorEvent(
            $this->loadQuestion(position: $this->getNavigationValueForQuestion($newAnswer->question), action: $action)
        );
        return true;
    }

    public function loadQuestionFromNav($position): bool
    {
        $nextQuestion = $this->questions->discussionTypeFiltered($this->openOnly)->get($position - 1);
        if (!$nextQuestion) {
            return false;
        }

        $answersForQuestion = $this->getAnswersForCurrentQuestion($nextQuestion);
        $hasAnswerForCurrentStudent = $answersForQuestion
            ->where('test_participant_id', $this->getCurrentParticipantId())
            ->first();

        if (!$hasAnswerForCurrentStudent) {
            $firstAvailableAnswer = $answersForQuestion->first();
            $this->setComponentAnswerProperties($firstAvailableAnswer, $this->getAnswerIndex($firstAvailableAnswer));
        }

        $this->loadQuestion($position);
        return true;
    }

    public function setWebspellcheckerEnabled(): void
    {
        $this->webSpellCheckerEnabled = auth()->user()->schoolLocation()->value('allow_wsc') ?? false;
    }

    private function dispatchScoreNotificationForQuestion(): void
    {
        if ($this->currentQuestion->isDiscussionTypeOpen || $this->currentQuestion->isType('Completion')) {
            return;
        }
        if ($this->scoreWarningDispatchedForQuestion) {
            return;
        }

        $this->scoreWarningDispatchedForQuestion = true;
        $this->dispatchBrowserEvent(
            'notify',
            ['message' => __('assessment.override_system_score_notification'), 'type' => 'error']
        );
    }

}