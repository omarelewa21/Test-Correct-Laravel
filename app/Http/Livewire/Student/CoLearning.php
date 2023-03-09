<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\Events\CoLearningForceTakenAway;
use tcCore\Events\CoLearningNextQuestion;
use tcCore\Events\CoLearningPresence;
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

    public $answerRating = null;
    public $answerRatingId;
    protected $answerRatings = null;
    public $answeredAnswerRatingIds;

    public $discussingQuestionId;

    protected $queryString = [
        'answerRatingId'     => ['as' => 'e'],
        'coLearningFinished' => ['except' => false, 'as' => 'b']
    ];

    public array $questionOrderList;

    public int $numberOfQuestions;
    public int $questionFollowUpNumber = 0; //with or without Closed questions
    public int $questionOrderNumber; //with all questions
    public int $numberOfAnswers;
    public int $answerFollowUpNumber = 0;

    public $testParticipant;
    public $answerOptions;

    protected function getListeners()
    {
        return [
//            CoLearningForceTakenAway::channelSignature($this->testParticipant->uuid) => 'redirectToTestTakesInReview',
//            CoLearningNextQuestion::channelSignature($this->testParticipant->uuid)   => 'goToActiveQuestion',
//            CoLearningPresence::channelSignature($this->testTake->uuid)              => 'updateHeartbeat',
            'UpdateAnswerRating'                                                     => 'updateAnswerRating',
        ];
    }

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;
        $this->discussingQuestionId = $this->testTake->discussing_question_id;
        $this->questionOrderList = $this->testTake->test->getQuestionOrderList();

        $this->redirectIfNotStatusDiscussing();

        $this->testParticipant = $this->testTake->testParticipants()
            ->where('user_id', auth()->id())
            ->first();
        $this->discussOpenQuestionsOnly = $this->testTake->discussion_type === 'OPEN_ONLY';

        if (!$this->coLearningFinished) {
            $this->getAnswerRatings();
        }
        $this->updateHeartbeat(false);
    }

    public function render()
    {
        if (is_null($this->answerRating) && (!$this->noAnswerRatingAvailableForCurrentScreen || !$this->coLearningFinished)) {
            $this->answerRating = AnswerRating::find($this->answerRatingId);
            $this->writeDiscussingAnswerRatingToDatabase();
        }
        $this->waitForTeacherNotificationEnabled = $this->shouldShowWaitForTeacherNotification();

        return view('livewire.student.co-learning')
            ->layout('layouts.co-learning-student');
    }

    public function booted()
    {
        if($this->testTake->test_take_status_id > 7){
            return $this->redirectToTestTakesInReview();
        }
    }

    public function redirectToTestTakesInReview()
    {
        return redirect()->route('student.test-takes', ['tab' => 'review']);
    }

    public function getEnableNextQuestionButtonProperty(): bool
    {
        if (!$this->answerRating->answer->isAnswered) {
            return true;
        }

        return isset($this->answerRating->rating);
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
    }

    public function goToNextAnswerRating(): void
    {
        $this->getAnswerRatings('next');
    }

    public function goToActiveQuestion(): void
    {
        $this->waitForTeacherNotificationEnabled = false;

        $this->getAnswerRatings();
    }

    /**
     * Rating update coming from score-slider
     * Updated Rating WireModel Lifecycle Hook
     */
    public function updatedRating(): void
    {
        $this->handleUpdatingRating();
    }

    /**
     * Rating update coming from ColearningQuestion Component
     * Update Rating from emit of child livewire Question component
     */
    public function updateAnswerRating($answerOptions): void
    {
        $questionIsFullyRated = $answerOptions['amountChecked'] === $answerOptions['amountCheckable'];

        if ($this->allowRatingWithHalfPoints) {
            $this->rating = round($this->maxRating / $answerOptions['maxScore'] * $answerOptions['score'] * 2)/2;
        } else {
            $this->rating = round($this->maxRating / $answerOptions['maxScore'] * $answerOptions['score']);
        }

        $this->handleUpdatingRating($questionIsFullyRated);

        $this->dispatchBrowserEvent('updated-score', ['score' => $this->rating]);
    }

    private function handleUpdatingRating($updateAnswerRatingRating = true)
    {
        if ((int)$this->rating < 0) {
            $this->rating = 0;
        }
        if ((int)$this->rating >= $this->maxRating) {
            $this->rating = $this->maxRating;
        }

        if($updateAnswerRatingRating){
            $this->answerRating->rating = $this->rating;
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
        $currentQuestionId = $this->testTake->discussingQuestion->getKey();

        $this->questionOrderNumber = $this->questionOrderList[$currentQuestionId];
        $this->numberOfQuestions = $testTakeQuestionsCollection->reduce(function ($carry, $question) use ($currentQuestionId) {
            if($question->discuss === 0) {
                return $carry;
            }

            if ($this->discussOpenQuestionsOnly && $question->canCheckAnswer()) { //question canCheckAnswer === 'Closed question'
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

        $this->answeredAnswerRatingIds = $this->answerRatings->filter(function($ar) {
            return $ar->answer->isAnswered;
        })->map->getKey()->values();

        if ($this->answerRatings->isNotEmpty()) {
            $this->noAnswerRatingAvailableForCurrentScreen = false;

            $this->setActiveAnswerRating($navigateDirection);

            $this->writeDiscussingAnswerRatingToDatabase();

            $this->setQuestionRatingProperties();

            $this->discussingQuestionId = $this->answerRating->answer->question_id;

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
        if ($this->waitForTeacherNotificationEnabled) {
            return true;
        }

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

    private function redirectIfNotStatusDiscussing()
    {
        if ($this->testTake->test_take_status_id !== TestTakeStatus::STATUS_DISCUSSING) {
            return redirect()->route('student.test-takes', ['tab' => 'discuss']);
        }
    }

    public function updateHeartbeat($skipRender = true)
    {
        $this->redirectIfNotStatusDiscussing();

        if($this->testTake->discussing_question_id !== $this->discussingQuestionId) {
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

    /* completion question specific */

    private function destroyCompletionQuestionSession()
    {
        if (session()->has(CompletionQuestion::SESSION_KEY)) {
            session()->forget(CompletionQuestion::SESSION_KEY);
        }
    }

}
