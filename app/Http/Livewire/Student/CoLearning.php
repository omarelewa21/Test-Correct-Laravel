<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\Http\Controllers\AnswerRatingsController;
use tcCore\Http\Controllers\TestTakeLaravelController;
use tcCore\Http\Livewire\CoLearning\CompletionQuestion;
use tcCore\Http\Livewire\CoLearning\OpenQuestion;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class CoLearning extends Component
{
    const SESSION_KEY = 'co-learning-answer-options';

    public ?TestTake $testTake;
    public bool $discussOpenQuestionsOnly = false;
    public bool $nextAnswerAvailable = false;
    public bool $previousAnswerAvailable = false;
    public $rating = null; //float or int depending on $questionAllowsDecimalScore
    public int $maxRating;

    public $allowRatingWithHalfPoints = false;

    public bool $noAnswerRatingAvailableForCurrentScreen = false;
    public bool $waitForTeacherNotificationEnabled = false;
    public bool $finishCoLearningButtonEnabled = false;
    public bool $coLearningFinished = false;

    public bool $scoreHasBeenManuallyChanged = false;

    public $answerRating = null;
    protected $answerRatings = null;
    public $answerRatingId;

    protected $queryString = ['answerRatingId', 'coLearningFinished'];

    public int $numberOfQuestions;
    public int $questionFollowUpNumber = 0;
    public int $numberOfAnswers;
    public int $answerFollowUpNumber = 0;

    protected function getListeners()
    {
        return [
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.CoLearningNextQuestion'   => 'goToNextQuestion',
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.CoLearningForceTakenAway' => 'redirectToTestTakesInReview', //.action, the dot is necessary
            'UpdateAnswerRating'                                                                          => 'updateAnswerRating',
        ];
    }

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;

        $this->redirectIfNotStatusDiscussing();

        $this->testParticipant = $this->testTake->testParticipants()
            ->where('user_id', auth()->id())
            ->first();
        $this->discussOpenQuestionsOnly = $this->testTake->discussion_type === 'OPEN_ONLY' ? true : false;

        if (!$this->coLearningFinished) {
            $this->getAnswerRatings();
            $this->setQuestionRatingProperties();
        }
    }

    public function render()
    {
        if (is_null($this->answerRating) && (!$this->noAnswerRatingAvailableForCurrentScreen || !$this->coLearningFinished)) {
            $this->answerRating = AnswerRating::find($this->answerRatingId);
        }

        $this->waitForTeacherNotificationEnabled = $this->shouldShowWaitForTeacherNotification();

        return view('livewire.student.co-learning')
            ->layout('layouts.co-learning');
    }

    public function redirectToTestTakesInReview()
    {
        return redirect()->route('student.test-takes', ['tab' => 'review']);
    }

    public function getEnableNextQuestionButtonProperty(): bool
    {
        switch ($this->answerRating->answer->question->type) {
            case 'CompletionQuestion':
                $data = $this->getAnswerOptionsFromSession();
                if ($data && isset($data['counts'])) {
                    $statement1 = (isset($this->rating) && !is_null($this->rating));

                    $statement2 = $data['counts']['amountCheckable'] === $data['counts']['amountChecked'];

                    return $statement1 && $statement2;
                }

            default:
                return (isset($this->rating) && !is_null($this->rating));
        }
    }

    public function destroyCompletionQuestionSession()
    {
        if (session()->has(CompletionQuestion::SESSION_KEY)) {
            session()->forget(CompletionQuestion::SESSION_KEY);
        }
    }

    public function goToFinishedCoLearningPage(): void
    {
        $this->coLearningFinished = true;

        $this->destroyCompletionQuestionSession();

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

        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);
    }

    public function goToNextAnswerRating(): void
    {
        $this->getAnswerRatings('next');

        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);
    }

    public function goToNextQuestion(): void
    {
        $this->getAnswerRatings();
        $this->setQuestionRatingProperties();

        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);
    }

    /**
     * Updated Rating WireModel Lifecycle Hook
     * @return void
     */
    public function updatedRating(): void
    {
        $this->handleUpdatingRating();

        $this->writeToSessionThatScoreHasBeenManuallyChanged();
    }

    /**
     * Update Rating from emit of child livewire Question component
     */
    public function updateAnswerRating(int $score, int $maxScore): void
    {
        if ($this->allowRatingWithHalfPoints) {
            $this->rating = $this->maxRating / $maxScore * $score;
        } else {
            $this->rating = round($this->maxRating / $maxScore * $score);
        }

        $this->handleUpdatingRating();

        $this->dispatchBrowserEvent('updated-score', ['score' => $this->rating]);
    }

    private function handleUpdatingRating()
    {
        if ((int)$this->rating < 0) {
            $this->rating = 0;
        }
        if ((int)$this->rating >= $this->maxRating) {
            $this->rating = $this->maxRating;
        }
        AnswerRating::whereId($this->answerRatingId)->update(['rating' => $this->rating]);

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
        $currentQuestionId = $this->testTake->discussingQuestion->getKey();

        $this->numberOfQuestions = $testTakeQuestionsCollection->reduce(function ($carry, $question) use ($currentQuestionId) {
            if ($this->discussOpenQuestionsOnly && !$question->discuss) {
                return $carry;
            }
            $carry++;
            if ($question->id == $currentQuestionId) {
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
            'filter' => [
                "discussing_at_test_take_id" => $this->testTake->uuid,
            ],
            'order'  => ['id' => 'asc']
        ];

        $request = new Request();
        $request->merge($params);

        $response = (new AnswerRatingsController())->indexFromWithin($request);
        $this->answerRatings = $response->getOriginalContent()->keyBy('id');

        if ($this->answerRatings->isNotEmpty()) {
            $this->noAnswerRatingAvailableForCurrentScreen = false;

            $this->setActiveAnswerRating($navigateDirection);
            $this->setWhichScoreSliderShouldBeShown();

            $this->setScoreHasBeenManuallyChanged();

            if ($this->answerRating->rating === null) {
                $this->rating = null;
            } else {
                $this->rating = $this->allowRatingWithHalfPoints ? $this->answerRating->rating : (int)$this->answerRating->rating;
            }

            $this->previousAnswerAvailable = $this->answerRatings->filter(fn($ar) => $ar->getKey() < $this->answerRatingId)->count() > 0;
            $this->nextAnswerAvailable = $this->answerRatings->filter(fn($ar) => $ar->getKey() > $this->answerRatingId)->count() > 0;

            $this->waitForTeacherNotificationEnabled = $this->shouldShowWaitForTeacherNotification();

            $this->answerRating->refresh();

        }
        if ($this->testTake->discussingQuestion->type === 'InfoscreenQuestion') {
            $this->noAnswerRatingAvailableForCurrentScreen = true;
            $this->waitForTeacherNotificationEnabled = true;
        }

        $this->getQuestionAndAnswerNavigationData();

    }

    private function checkIfStudentCanFinishCoLearning(): void
    {
        if (
            $this->numberOfQuestions === $this->questionFollowUpNumber &&
            $this->shouldShowWaitForTeacherNotification()
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
        if (isset($this->answerRatings)) {
            return !$this->answerRatings->map->rating->contains(null); //show on both answerRatings after both are rated
        }

        if ($this->waitForTeacherNotificationEnabled) {
            return true; // when updating answerRating, 'answerRatings'  is not available...
        }

        return (!$this->nextAnswerAvailable && isset($this->rating)); //show on last answerRating after both are rated
    }

    private function redirectIfNotStatusDiscussing()
    {
        if ($this->testTake->test_take_status_id !== TestTakeStatus::STATUS_DISCUSSING) {
            return redirect()->route('student.test-takes', ['tab' => 'discuss']);
        }
    }

    private function getAnswerOptionsFromSession(): array|false
    {
        if (session()->has(static::SESSION_KEY)) {
            if (isset(session()->get(static::SESSION_KEY)[$this->answerRatingId])) {
                return session()->get(static::SESSION_KEY)[$this->answerRatingId];
            }
        }
        return false;
    }

    private function writeToSessionThatScoreHasBeenManuallyChanged()
    {
        if (session()->has(static::SESSION_KEY)) {
            if (isset(session()->get(static::SESSION_KEY)[$this->answerRatingId])) {
                $sessionData = session()->get(static::SESSION_KEY)[$this->answerRatingId];
                $sessionData['scoreManuallyChanged'] = true;

                session([static::SESSION_KEY => $sessionData]);
                return true;
            }
        }
        return false;
    }

    private function setScoreHasBeenManuallyChanged() : void
    {
        $data = $this->getAnswerOptionsFromSession();

        $this->scoreHasBeenManuallyChanged = isset($data['scoreManuallyChanged']) ? true : false;
    }
}
