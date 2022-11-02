<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\Http\Controllers\AnswerRatingsController;
use tcCore\Http\Controllers\TestTakeLaravelController;
use tcCore\Http\Livewire\CoLearning\OpenQuestion;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class CoLearning extends Component
{
    public ?TestTake $testTake;
    public bool $discussOpenQuestionsOnly = false;
    public bool $nextAnswerAvailable = false;
    public bool $previousAnswerAvailable = false;
    public $rating = null; //float or int depending on $questionAllowsDecimalScore
    public int $maxRating;

    public $questionAllowsDecimalScore = false;

    public bool $noAnswerRatingAvailableForCurrentScreen = false;
    public bool $waitForTeacherNotificationEnabled = false;
    public bool $finishCoLearningButtonEnabled = false;
    public bool $coLearningFinished = false;

    public string $testName = 'test;';

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
        if ($test_take->test_take_status_id !== TestTakeStatus::STATUS_DISCUSSING) {
            return redirect()->route('student.test-takes', ['tab' => 'discuss']);
        } //todo refactor to method

        $this->testTake = $test_take;
        $this->testParticipant = $this->testTake->testParticipants()
            ->where('user_id', auth()->id())
            ->first();
        $this->discussOpenQuestionsOnly = $this->testTake->discussion_type === 'OPEN_ONLY' ? true : false;

        if (!$this->coLearningFinished) {
            $this->getAnswerRatings();
            $this->setRatingProperties();
        }
    }

    public function render()
    {
        if (is_null($this->answerRating) && (!$this->noAnswerRatingAvailableForCurrentScreen || !$this->coLearningFinished)) {
            $this->answerRating = AnswerRating::find($this->answerRatingId);
        }

        return view('livewire.student.co-learning')
            ->layout('layouts.co-learning');
    }

    public function redirectToTestTakesInReview()
    {
        return redirect()->route('student.test-takes', ['tab' => 'review']);
    }

    public function goToFinishedCoLearningPage()
    {
        $this->coLearningFinished = true;

        $this->waitForTeacherNotificationEnabled = false;
        $this->answerRatingId = null;
        $this->answerRating = null;

        //todo set all answerRating properties etc to null/false?
    }

    public function goToPreviousAnswerRating()
    {
        if (!$this->previousAnswerAvailable) {
            return;
        }
        $this->getAnswerRatings('previous');

        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);
    }

    public function goToNextAnswerRating()
    {
        $this->getAnswerRatings('next');

        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);
    }

    public function goToNextQuestion()
    {
        $this->getAnswerRatings();
        $this->setRatingProperties();

        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);
    }

    public function setRatingProperties(): void
    {
        $this->maxRating = $this->answerRating->answer->question->score;

        $this->questionAllowsDecimalScore = (bool)$this->answerRating->answer->question->decimal_score;

        $this->continuousScoreSlider = false; //todo refactor to method?
        if ($this->questionAllowsDecimalScore && $this->maxRating > 7) {
            $this->continuousScoreSlider = true;
        }
        if (!$this->questionAllowsDecimalScore && $this->maxRating > 15) {
            $this->continuousScoreSlider = true;
        }

        $this->rating = $this->questionAllowsDecimalScore ? $this->answerRating->rating : (int)$this->answerRating->rating;

    }

    public function updatedRating()
    {
        if ((int)$this->rating < 0) {
            $this->rating = 0;
        }
        if ((int)$this->rating > $this->maxRating) {
            $this->rating = $this->maxRating;
        }
        $this->answerRating = AnswerRating::find($this->answerRatingId);

        $this->answerRating->update(['rating' => $this->rating]);

        // $this->answerRatings is missing here...
        $this->checkIfStudentCanFinishCoLearning(); //todo does not update / render the button
    }

    public function updateAnswerRating($amountChecked, $amountPossibleOptions)
    {
        if ($this->questionAllowsDecimalScore) {
            $this->rating = $this->maxRating / $amountPossibleOptions * $amountChecked;
        } else {
            $this->rating = round($this->maxRating / $amountPossibleOptions * $amountChecked);
        }

        $this->updatedRating();

        $this->dispatchBrowserEvent('updated-score', ['score' => $this->rating]);
    }

//    public function sendWaitForTeacherNotification()
//    {
//        return $this->dispatchBrowserEvent('notify', ['message' => __('co-learning.wait_for_teacher'), 'type' => 'error']);
//    }

    protected function getQuestionAndAnswerNavigationData()
    {
        $testTakeQuestionsCollection = TestTakeLaravelController::getData(null, $this->testTake);
        $currentQuestionId = $this->testTake->discussingQuestion->getKey();

        $this->numberOfQuestions = $testTakeQuestionsCollection->reduce(function ($carry, $question) use ($currentQuestionId) {
            if($this->discussOpenQuestionsOnly && !$question->discuss){
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

    protected function getAnswerRatings($navigateDirection = null)
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

    protected function checkIfStudentCanFinishCoLearning(): void
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

    protected function setActiveAnswerRating($navigateDirection): void
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
}
