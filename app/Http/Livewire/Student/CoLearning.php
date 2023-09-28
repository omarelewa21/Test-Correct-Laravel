<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use tcCore\AnswerRating;
use tcCore\Events\CommentedAnswerUpdated;
use tcCore\Events\TestTakeChangeDiscussingQuestion;
use tcCore\Events\CoLearningForceTakenAway;
use tcCore\Events\TestTakeCoLearningPresenceEvent;
use tcCore\Events\TestTakeForceTakenAway;
use tcCore\Events\TestTakeLeave;
use tcCore\Events\TestTakePresenceEvent;
use tcCore\Events\TestTakeStop;
use tcCore\Http\Controllers\AnswerRatingsController;
use tcCore\Http\Controllers\TestTakeLaravelController;
use tcCore\Http\Enums\AnswerFeedbackFilter;
use tcCore\Http\Helpers\CoLearningHelper;
use tcCore\Http\Livewire\CoLearning\CompletionQuestion;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\Questions\WithCompletionConversion;
use tcCore\Http\Traits\WithInlineFeedback;
use tcCore\InfoscreenQuestion;
use tcCore\MatchingQuestion;
use tcCore\Question;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class CoLearning extends TCComponent
{
    use WithInlineFeedback;
    use WithCompletionConversion;

    const SESSION_KEY = 'co-learning-answer-options';

    public ?TestTake $testTake;
    public bool $discussOpenQuestionsOnly = false;
    public bool $nextAnswerAvailable = false;
    public bool $previousAnswerAvailable = false;
    public $rating = null; //float or int depending on $questionAllowsDecimalScore
    public float $maxRating;

    public bool $nextQuestionAvailable = false;
    public bool $previousQuestionAvailable = false;

    public $allowRatingWithHalfPoints = false;

    public bool $noAnswerRatingAvailableForCurrentScreen = false;
    public bool $waitForTeacherNotificationEnabled = false;
    public bool $finishCoLearningButtonEnabled = false;
    public bool $coLearningFinished = false;

    public $answerRating = null;
    public $answerRatingId;
    protected $answerRatings = null;
    public $answeredAnswerRatingIds;

    public $discussingQuestionId;

    public $pollingFallbackActive = false;

    public $answered = false;
    public int $answerRatingsCount = 0;
    public array $answerRatingsRated = [];

    //Student navigation on their own pace, but not without teacher present
    public $selfPacedNavigation = false;

    protected $queryString = [
        'answerRatingId'     => ['as' => 'e'],
        'coLearningFinished' => ['except' => false, 'as' => 'b']
    ];

    public array $questionOrderList;

    public int $numberOfQuestions;
    public int $questionFollowUpNumber = 0; //with or without Closed questions
    public int $questionOrderNumber; //with all questions (without not discussed questions)
    public int $questionOrderAsInTestNumber; //with all questions types and also not discussed questions (like in test)
    public int $numberOfAnswers;
    public int $answerFollowUpNumber = 0;

    public $testParticipant;
    public $answerOptions;

    public $necessaryAmountOfAnswerRatings;
    public $group;
    public ?string $answeredStatus;
    public string $uniqueKey;

    /**
     * @return bool
     */
    public function getAtLastQuestionProperty(): bool
    {
        return $this->numberOfQuestions === $this->questionFollowUpNumber;
    }

    public function allAnswerRatingsFullyRated(): bool
    {
        if (isset($this->answerRatings)) {
            return $this->answerRatings->reduce(function ($carry, $answerRating) {
                if ($answerRating->rating === null && $answerRating->answer->isAnswered) {
                    $carry = false;
                }
                return $carry;
            }, true);
        }

        return (!$this->nextAnswerAvailable && isset($this->answerRating->rating));
    }

    protected function getListeners()
    {
        $listeners = [
            TestTakeCoLearningPresenceEvent::channelSignature(testTakeUuid: $this->testTake->uuid)  => 'render',
            TestTakeStop::channelSignature(testTakeUuid: $this->testTake->uuid)                     => 'redirectToTestTakesInReview',
            TestTakeLeave::channelSignature(testTakeUuid: $this->testTake->uuid)                    => 'redirectToTestTakesToBeDiscussed',
            'UpdateAnswerRating'                                                                    => 'updateAnswerRating', //old code?
            'goToActiveQuestion',
        ];

        if(!$this->selfPacedNavigation) {
            $listeners = array_merge($listeners, [
                TestTakeChangeDiscussingQuestion::channelSignature(testTakeUuid: $this->testTake->uuid) => 'goToActiveQuestion',
            ]);
        }

        return $listeners;
    }

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take->load('discussingQuestion');
        $this->testParticipant = $this->testTake->testParticipants()
                                                ->where('user_id', auth()->id())
                                                ->first();

        $this->selfPacedNavigation = $this->testTake->enable_student_navigation_colearning;
        $this->discussingQuestionId = $this->getDiscussingQuestion()->getKey();
        $this->questionOrderList = $this->testTake->test->getQuestionOrderListWithDiscussionType();
        $this->questionOrderNumber = $this->questionOrderList[$this->discussingQuestionId]['order'];
        $this->questionOrderAsInTestNumber = $this->questionOrderList[$this->discussingQuestionId]['order_in_test'];

        if (!$this->testTake->schoolLocation->allow_new_co_learning_teacher) {
            //if teacher is in old co-learning, polling needs to start, because the Pusher presence channel is not working in the old CO-Learning
            $this->pollingFallbackActive = true;
        }

        if ($this->redirectIfTestTakeInIncorrectState() instanceof Redirector) {
            return false;
        };


        $this->discussOpenQuestionsOnly = $this->testTake->discussion_type === 'OPEN_ONLY';

        if (!$this->coLearningFinished) {
            $this->getAnswerRatings();
            $this->necessaryAmountOfAnswerRatings = $this->answerRatings->count() ?: 1;
        }
    }

    public function render()
    {
        if (is_null($this->answerRating) && (!$this->noAnswerRatingAvailableForCurrentScreen || !$this->coLearningFinished)) {
            $this->answerRating = AnswerRating::find($this->answerRatingId);
            $this->writeDiscussingAnswerRatingToDatabase();
        }
        $this->waitForTeacherNotificationEnabled = $this->shouldShowWaitForTeacherNotification();

        $this->uniqueKey = $this->answerRating?->getKey() ?? '' .'-'. $this->questionFollowUpNumber .'-'. $this->answerFollowUpNumber;

        return view('livewire.student.co-learning')
            ->layout('layouts.co-learning-student');
    }

    public function booted()
    {
        if ($this->redirectIfTestTakeInIncorrectState() instanceof Redirector) {
            return false;
        };

        $this->answerFeedbackFilter = AnswerFeedbackFilter::CURRENT_USER;
        $this->selfPacedNavigation = $this->testTake->enable_student_navigation_colearning;
    }

    public function redirectToTestTakesInReview()
    {
        return redirect()->route('student.test-takes', ['tab' => 'review']);
    }

    public function redirectToTestTakesToBeDiscussed()
    {
        return redirect()->route('student.test-takes', ['tab' => 'discuss']);
    }

    public function redirectToWaitingRoom()
    {
        return redirect()->route('student.waiting-room', ['take' => $this->testTake->uuid]);
    }

    public function isNextAnswerRatingButtonDisabled(): bool
    {
        return !$this->nextAnswerAvailable
            || (!isset($this->answerRating->rating) && $this->answerRating->answer->isAnswered);
    }

    public function isPreviousAnswerRatingButtonDisabled(): bool
    {
        return !$this->previousAnswerAvailable;
    }

    public function isNextQuestionButtonDisabled(): bool
    {

        return !$this->nextQuestionAvailable
            || (collect($this->answerRatingsRated)->count() !== $this->answerRatingsCount);
    }

    public function isPreviousQuestionButtonDisabled(): bool
    {
        return !$this->previousQuestionAvailable;
    }

    protected function getPreviousQuestionData(): ?array
    {
        return collect($this->questionOrderList)
            ->filter(fn($item) => $item['order'] < $this->questionOrderList[$this->getDiscussingQuestion()->getKey()]['order'])
            ->sortByDesc('order')
            ->first();
    }

    protected function getNextQuestionData(): ?array
    {
        return collect($this->questionOrderList)
            ->filter(fn($item) => $item['order'] > $this->questionOrderList[$this->getDiscussingQuestion()->getKey()]['order'])
            ->sortBy('order')
            ->first();
    }

    public function goToFinishedCoLearningPage(): void
    {
        $this->coLearningFinished = true;

        $this->waitForTeacherNotificationEnabled = false;
        $this->answerRatingId = null;
        $this->answerRating = null;
    }

    public function goToPreviousAnswerRating(): void
    {
        if (!$this->previousAnswerAvailable) {
            return;
        }
        $this->getAnswerRatings('previous');
    }

    public function goToNextAnswerRating(): void
    {
        $this->getAnswerRatings('next');
    }

    public function goToNextQuestion()
    {
        if(!$this->selfPacedNavigation) {
            return;
        }

        CoLearningHelper::nextQuestion(
            testTake: $this->testTake,
            testParticipant: $this->testParticipant,
        );
        $this->testParticipant->refresh();

        $this->getAnswerRatings();
    }

    public function goToPreviousQuestion()
    {
        if(!$this->selfPacedNavigation) {
            return;
        }

        if ($previousQuestionId = $this->getPreviousQuestionData()['id']) {
            //set the previous question as the new discussing question
            $this->selfPacedNavigation
                ? $this->testParticipant->update(['discussing_question_id' => $previousQuestionId])
                : $this->testTake->update(['discussing_question_id' => $previousQuestionId]);
        }
        $this->testParticipant->refresh();

        $this->getAnswerRatings();
    }

    /**
     * @param mixed $discussingQuestionId
     * @return mixed|null
     */
    protected function getGroupQuestionIdForSubQuestion(mixed $questionId): null|int
    {
        return DB::query()
                 ->select('group_question_questions.group_question_id')
                 ->from('test_takes')
                 ->join('tests', 'tests.id', '=', 'test_takes.test_id')
                 ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                 ->join(
                     'group_question_questions',
                     'group_question_questions.group_question_id',
                     '=',
                     'test_questions.question_id'
                 )
                 ->where('test_takes.id', '=', $this->testTake->getKey())
                 ->where('group_question_questions.question_id', '=', $questionId)->first()?->group_question_id;
    }

    public function goToActiveQuestion(): void
    {
        if ($this->selfPacedNavigation) {
            return;
        }

        $this->waitForTeacherNotificationEnabled = false;

        $this->getAnswerRatings();
    }

    /**
     * Rating update coming from score-slider
     * Updated Rating WireModel Lifecycle Hook
     */
    public function updatedRating(): void
    {
        $this->handleUpdatingRatingAndJson();

        if($this->rating !== null && $this->rating !== "") {
            $this->answerRatingsRated = array_merge($this->answerRatingsRated, [$this->answerRating->getKey()]);
        }
    }

    /**
     * Rating update coming from ColearningQuestion Component
     * Update Rating from emit of child livewire Question component
     */
    public function updateAnswerRating(array $json, bool $fullyRated, int|float $rating): void
    {
        $this->setRating($rating);

        $this->handleUpdatingRatingAndJson($fullyRated, $json);

        $this->dispatchBrowserEvent('updated-score', ['score' => $this->rating]);
    }

    private function handleUpdatingRatingAndJson($updateAnswerRatingRating = true, $json = null)
    {
        if ((int)$this->rating < 0) {
            $this->rating = 0;
        }
        if ((int)$this->rating >= $this->maxRating) {
            $this->rating = $this->maxRating;
        }

        if ($json) {
            $this->answerRating->json = $json;
        }
        if ($updateAnswerRatingRating) {
            $this->answerRating->rating = $this->rating;
        }

        if ($updateAnswerRatingRating || $json) {
            $this->answerRating->save();
        }

        $this->checkIfStudentCanFinishCoLearning();
    }

    private function setQuestionRatingProperties(): void
    {
        $this->maxRating = $this->answerRating->answer->question->score;

        $this->setWhichScoreSliderShouldBeShown();
    }

    private function setWhichScoreSliderShouldBeShown(): void
    {
        $this->allowRatingWithHalfPoints = (bool)$this->answerRating->answer->question->decimal_score;
    }

    private function getQuestionAndAnswerNavigationData(): void
    {

        $testTakeQuestionsCollection = TestTakeLaravelController::getData(null, $this->testTake);
        $this->discussingQuestionId = $this->getDiscussingQuestion()->getKey();

        $this->questionOrderNumber = $this->questionOrderList[$this->discussingQuestionId]['order'];
        $this->questionOrderAsInTestNumber = $this->questionOrderList[$this->discussingQuestionId]['order_in_test'];
        $this->numberOfQuestions = $testTakeQuestionsCollection->reduce(function ($carry, $question) {
            if ($question->belongs_to_carousel) {
                //carousel questions are not discussed during co-learning
                return $carry;
            }
            if ($question->discuss === 0) {
                return $carry;
            }

            if ($this->discussOpenQuestionsOnly && $question->canCheckAnswer()) { //question canCheckAnswer === 'Closed question'
                return $carry;
            }
            $carry++;
            if ($question->id == $this->discussingQuestionId) {
                $this->questionFollowUpNumber = $carry;
            }
            return $carry;
        }, 0);

        if ($this->noAnswerRatingAvailableForCurrentScreen) {
            $this->numberOfAnswers = 1;
            $this->answerFollowUpNumber = 1;
            return;
        }

        $answersForUserAndCurrentQuestion = $this->answerRatings->map->answer;

        $this->numberOfAnswers = $this->answerRatings->count();

        if ($this->numberOfAnswers != 0) {
            $answersForUserAndCurrentQuestion->reduce(function ($carry, $answer) {
                $carry++;
                if ($answer->id == $this->answerRating->answer->id) {
                    $this->answerFollowUpNumber = $carry;
                }
                return $carry;
            }, 0);
        }

        $this->checkIfStudentCanFinishCoLearning();
    }

    private function getAnswerRatings($navigateDirection = null): void
    {
        $params = [
            'mode'   => 'all',
            'with'   => ['questions'],
            'filter' => [],
            'order'  => ['id' => 'asc']
        ];
        $this->selfPacedNavigation
            ? $params['filter']['discussing_at_test_participant_id'] = $this->testParticipant->uuid
            : $params['filter']['discussing_at_test_take_id'] = $this->testTake->uuid;

        $request = new Request();
        $request->merge($params);

        $response = (new AnswerRatingsController())->indexFromWithin($request);
        $this->answerRatings = $response->getOriginalContent()->keyBy('id');

        if ($this->answerRatings->count() < $this->necessaryAmountOfAnswerRatings) {
            // this fixes an error when the answerRatings are queried before the teacher is done generating them.
            $this->emitSelf('goToActiveQuestion');
            $this->skipRender();
        }

        $this->answeredAnswerRatingIds = $this->answerRatings->filter(function ($ar) {
            return $ar->answer->isAnswered;
        })->map->getKey()->values();

        if ($this->answerRatings->isNotEmpty()) {
            $this->noAnswerRatingAvailableForCurrentScreen = false;

            $this->setActiveAnswerRating($navigateDirection);
            $this->answered = $this->answerRating->answer->isAnswered;
            $this->answeredStatus = $this->answerRating->answer->answeredStatus;
            $this->answerRatingsRated = $this->answerRatings->filter(function ($ar) {
                return $ar->rating !== null || !$ar->answer->isAnswered;
            })->map->getKey()->values()->toArray();
            $this->answerRatingsCount = $this->answerRatings->count();

            $this->writeDiscussingAnswerRatingToDatabase();

            $this->setQuestionRatingProperties();

            $this->discussingQuestionId = $this->answerRating->answer->question_id;
            $this->group = $this->answerRating->answer->question->getGroupQuestion($this->testTake);

            if ($this->answerRating->rating === null) {
                $this->rating = null;
            } else {
                $this->rating = $this->allowRatingWithHalfPoints ? $this->answerRating->rating : round($this->answerRating->rating);
            }

            $this->previousAnswerAvailable = $this->answerRatings->filter(fn($ar) => $ar->getKey() < $this->answerRatingId)->count() > 0;
            $this->nextAnswerAvailable = $this->answerRatings->filter(fn($ar) => $ar->getKey() > $this->answerRatingId)->count() > 0;



            $this->waitForTeacherNotificationEnabled = $this->shouldShowWaitForTeacherNotification();

            $this->answerRating->refresh();

        }
        if ($this->getDiscussingQuestion() instanceof InfoscreenQuestion) {
            $this->noAnswerRatingAvailableForCurrentScreen = true;
            $this->waitForTeacherNotificationEnabled = true;
        }
        if ($this->answerRatings->isNotEmpty()) {
            $this->getQuestionAndAnswerNavigationData();

            $this->previousQuestionAvailable = $this->discussOpenQuestionsOnly
                ? collect($this->questionOrderList)->get($this->getDiscussingQuestion()->getKey())['order_open_only'] > 1
                : collect($this->questionOrderList)->get($this->getDiscussingQuestion()->getKey())['order'] > 1;


            $this->nextQuestionAvailable = $this->discussOpenQuestionsOnly
                ? collect($this->questionOrderList)->get($this->getDiscussingQuestion()->getKey())['order_open_only'] < $this->numberOfQuestions
                : collect($this->questionOrderList)->get($this->getDiscussingQuestion()->getKey())['order'] < $this->numberOfQuestions;
        }

        $this->getSortedAnswerFeedback();
    }

    private function checkIfStudentCanFinishCoLearning(): void
    {
        if (
            $this->atLastQuestion &&
            ($this->selfPacedNavigation ? $this->allAnswerRatingsFullyRated() : $this->shouldShowWaitForTeacherNotification() )
        ) {
            $this->finishCoLearningButtonEnabled = true;
            return;
        }
        $this->finishCoLearningButtonEnabled = false;
    }

    private function setActiveAnswerRating(?string $navigateDirection): void
    {
        if (isset($this->answerRatingId)) {
            if ($navigateDirection == 'next' && $this->nextAnswerAvailable) {
                $this->answerRating = $this->answerRatings->filter(fn($ar) => $ar->getKey() > $this->answerRatingId)->first();
                $this->answerRatingId = $this->answerRating->getKey();
                return;
            }
            if ($navigateDirection == 'previous' && $this->previousAnswerAvailable) {
                $this->answerRating = $this->answerRatings->filter(fn($ar) => $ar->getKey() < $this->answerRatingId)->last();
                $this->answerRatingId = $this->answerRating->getKey();
                return;
            }

            if ($this->answerRatings->map->id->contains($this->answerRatingId)) {
                $this->answerRating = $this->answerRatings->where('id', $this->answerRatingId)->first();
                return;
            }
        }
        $this->answerRating = $this->answerRatings->first();
        $this->answerRatingId = $this->answerRating->getKey();

    }

    private function shouldShowWaitForTeacherNotification(): bool
    {
        if($this->finishCoLearningButtonEnabled) {
            return false;
        }

        if ($this->waitForTeacherNotificationEnabled) {
            return true;
        }

        return $this->allAnswerRatingsFullyRated();
    }

    private function redirectIfTestTakeInIncorrectState(): Redirector|false
    {
        if ($this->getDiscussingQuestion()->getKey() === null) {
            return $this->redirectToWaitingRoom();
        }
        if ($this->testTake->test_take_status_id < TestTakeStatus::STATUS_DISCUSSING) {
            return $this->redirectToTestTakesToBeDiscussed();
        }
        if ($this->testTake->test_take_status_id > TestTakeStatus::STATUS_DISCUSSING) {
            return $this->redirectToTestTakesInReview();
        }
        return false;
    }

    public function updateHeartbeat($skipRender = true)
    {
        if ($this->redirectIfTestTakeInIncorrectState() instanceof Redirector) {
            return false;
        };

        if (!$this->selfPacedNavigation && $this->getDiscussingQuestion()->getKey() !== $this->discussingQuestionId) {
            return $this->goToActiveQuestion();
        }

        if ($skipRender) {
            $this->skipRender();
        }

        return $this->testParticipant->setAttribute('heartbeat_at', Carbon::now())->save();
    }

    public function getQuestionComponentNameProperty(): string
    {
        return str($this->answerRating->answer->question->type)->kebab()->prepend('co-learning.')->value;
    }

    private function writeDiscussingAnswerRatingToDatabase(): void
    {
        if ($this->testParticipant->discussing_answer_rating_id !== $this->answerRatingId) {
            $this->testParticipant->update(['discussing_answer_rating_id' => $this->answerRatingId]);
        }
    }

    //alias property name for inline feedback
    public function getCurrentQuestionProperty()
    {
        return $this->getDiscussingQuestion();
    }

    public function getDiscussingQuestion()
    {
        return $this->selfPacedNavigation
            ? ($this->testParticipant->discussingQuestion ?? $this->testTake->discussingQuestion)
            : $this->testTake->discussingQuestion;
    }

    public function toggleValueUpdated(int $id, string $state, int|float $value): void
    {
        $json = $this->answerRating?->fresh()?->json ?? [];

        $json[$id] = $state === 'on';

        $correctAnswerStructure = $this->getCurrentQuestion()->getCorrectAnswerStructure();

        switch (strtolower($this->getCurrentQuestion()->type . '-' . $this->getCurrentQuestion()->subtype)) {
            case 'matchingquestion-classify':
            case 'matchingquestion-matching':
                $correctAnswerStructure = $correctAnswerStructure->filter(fn($answer) => $answer->type === 'RIGHT');

                $scorePerToggle = round($this->maxRating / $correctAnswerStructure->count(), 2);
                $ratingPerAnswerOption = $correctAnswerStructure->mapWithKeys(fn($answerData) => [$answerData->id => $scorePerToggle]);

                $amountOfToggles = collect(json_decode($this->answerRating->answer->json, true))
                    ->filter(fn($value, $key) => $key === 'order' ? false : $value)->count();
                break;
            case 'completionquestion-multi':
            case 'completionquestion-completion':
                $correctAnswerStructure = $correctAnswerStructure->filter(fn($answer) => $answer->correct)->unique('tag');

                $scorePerToggle = round($this->maxRating / $correctAnswerStructure->count(), 2);
                $ratingPerAnswerOption = $correctAnswerStructure->mapWithKeys(fn($answerData) => [$answerData->tag => $scorePerToggle]);

                $amountOfToggles = collect(json_decode($this->answerRating->answer->json, true))
                    ->filter(fn($value, $key) => $value)->count();
                break;
            case 'multiplechoicequestion-multiplechoice':
                if ($this->getCurrentQuestion()->all_or_nothing) {
                    $this->updateAnswerRating(
                        json      : $json,
                        fullyRated: true,
                        rating    : array_values($json)[0] ? $this->maxRating : 0
                    );
                    return;
                }

                $scorePerToggle = round($this->maxRating / $this->getCurrentQuestion()->selectable_answers, 2);
                $ratingPerAnswerOption = $correctAnswerStructure->mapWithKeys(fn($answerData) => [$answerData->order => $scorePerToggle]);

                $amountOfToggles = collect(json_decode($this->answerRating->answer->json, true))->filter(fn($value) => $value)->count();
                break;
            case 'multiplechoicequestion-truefalse':
                $this->updateAnswerRating(
                    json      : $json,
                    fullyRated: true,
                    rating    : array_values($json)[0] ? $this->maxRating : 0
                );
                return;
            default:
                $ratingPerAnswerOption = [];
        }
        $amountOfTogglesUsed = collect($json)->count();

        $this->updateAnswerRating(
            json      : $json,
            fullyRated: $amountOfTogglesUsed === $amountOfToggles,
            rating    : collect($json)
                            ->filter(fn($value) => $value)
                            ->reduce(fn($carry, $value, $key) => $carry + ($ratingPerAnswerOption[$key] ?? 0), 0)
        //first filter out all false values (toggle == off), then reduce the remaining values to a single value
        );
        if($amountOfTogglesUsed === $amountOfToggles) {
            $this->answerRatingsRated = array_unique(array_merge($this->answerRatingsRated, [$this->answerRating->getKey()]));
        }
    }

    private function setRating(int|float $rating)
    {
        $this->rating = $this->allowRatingWithHalfPoints
            ? round($rating * 2) / 2
            : round($rating);
    }
}
