<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\Http\Controllers\AnswerRatingsController;
use tcCore\Http\Controllers\TestTakeLaravelController;
use tcCore\Http\Livewire\CoLearning\OpenQuestion;
use tcCore\TestTake;

class CoLearning extends Component
{
    public ?TestTake $testTake;
    public bool $nextAnswerAvailable = false;
    public bool $previousAnswerAvailable = false;
    public $rating = null; //float or int
    public int $maxRating;

    public $questionAllowsDecimalScore = false;

    public bool $informationScreenQuestion = false;
    public bool $studentWaitForTeacher = false;
    public bool $finishCoLearningButtonEnabled = false;

    public string $testName = 'test;';

    protected $answerRating = null;
    protected $answerRatings = null;
    public $answerRatingId;

    protected $queryString = ['answerRatingId'];

    public int $numberOfQuestions;
    public int $questionFollowUpNumber = 0;
    public int $numberOfAnswers;
    public int $answerFollowUpNumber = 0;

    /**
     * @return void
     */
    public function setActiveAnswerRating($navigateDirection): void
    {
        //check if answerRatingId is valid? if answerRatings->contains the id?

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

            if (isset($this->answerRatingId) && $this->answerRatings->map->id->contains($this->answerRatingId)) {
                $this->answerRating = $this->answerRatings->where('id', $this->answerRatingId)->first();
                return;
            }
        }
        $this->answerRating = $this->answerRatings->first();
        $this->answerRatingId = $this->answerRating->getKey();

    }

    /**
     * @return void
     */
    public function checkIfStudentCanFinishCoLearning(): void
    {
        if (
            $this->numberOfQuestions === $this->questionFollowUpNumber &&
            $this->studentWaitForTeacher
        ) {
            $this->finishCoLearningButtonEnabled = true;
            return;
        }
        $this->finishCoLearningButtonEnabled = false;
    }

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
        if ($test_take->test_take_status_id !== 7) {
            return redirect()->route('student.test-takes', ['tab' => 'discuss']);
        }
        $this->testTake = $test_take;
        $this->testParticipant = $test_take->testParticipants->where('user_id', auth()->id())->first();

        $this->getAnswerRatings();
    }

    public function render()
    {
        if (is_null($this->answerRating)) {
            $this->answerRating = AnswerRating::find($this->answerRatingId);
        }

        return view('livewire.student.co-learning')
            ->layout('layouts.co-learning');
    }

    public function updatedRating()
    {
        if ($this->rating < 0 || $this->rating > $this->maxRating) {
            throw new \Exception('Supplied rating is out of bounds.');
        }
        $this->answerRating = AnswerRating::find($this->answerRatingId);

        $this->answerRating->update(['rating' => $this->rating]);

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

    public function sendWaitForTeacherNotification()
    {
        return $this->dispatchBrowserEvent('notify', ['message' => __('co-learning.wait_for_teacher'), 'type' => 'error']);
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

        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);
    }

    protected function getQuestionAndAnswerNavigationData()
    {
        $testTakeQuestionsCollection = TestTakeLaravelController::getData(null, $this->testTake);
        $currentQuestionId = $this->testTake->discussingQuestion->getKey();

        //todo remove not discussed closed questions from count and followupnumber

        $this->numberOfQuestions = $testTakeQuestionsCollection->count();

        $testTakeQuestionsCollection->reduce(function ($carry, $question) use ($currentQuestionId) {
            $carry++;
            if ($question->id == $currentQuestionId) {
                $this->questionFollowUpNumber = $carry;
            }
            return $carry;
        }, 0);

        if ($this->informationScreenQuestion) {
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
//                "rated"                      => "0",
            ],
            'order'  => ['id' => 'asc'] //make sure order of answerRatings is allways the same
        ];

        $request = new Request();
        $request->merge($params);

        $response = (new AnswerRatingsController())->indexFromWithin($request);
        $this->answerRatings = $response->getOriginalContent()->keyBy('id');
        //    386 => tcCore\AnswerRating {#2539 ▶}
        //    391 => tcCore\AnswerRating {#2574 ▶}

        if ($this->answerRatings->isNotEmpty()) {
            $this->informationScreenQuestion = false;

            $this->setActiveAnswerRating($navigateDirection);

            $this->questionAllowsDecimalScore = (bool)$this->answerRating->answer->question->decimal_score;

            $this->rating = $this->questionAllowsDecimalScore ? $this->answerRating->rating : (int)$this->answerRating->rating;

            $this->previousAnswerAvailable = $this->answerRatings->filter(fn($ar) => $ar->getKey() < $this->answerRatingId)->count() > 0;
            $this->nextAnswerAvailable = $this->answerRatings->filter(fn($ar) => $ar->getKey() > $this->answerRatingId)->count() > 0;

            $this->studentWaitForTeacher = $this->shouldShowWaitForTeacherNotification();

            $this->maxRating = $this->answerRating->answer->question->score;

            $this->answerRating->refresh();


        }
        if ($this->testTake->discussingQuestion->type === 'InfoscreenQuestion') {
            $this->informationScreenQuestion = true;
            $this->studentWaitForTeacher = true;
        }


        $this->getQuestionAndAnswerNavigationData();

    }

    public function redirectToTestTakesInReview()
    {
        return redirect()->route('student.test-takes', ['tab' => 'review']);
    }

    public function getCannotViewFooterInformationProperty()
    {
        return ($this->informationScreenQuestion);
    }

    private function shouldShowWaitForTeacherNotification(): bool
    {
        return !$this->answerRatings->map->rating->contains(null); //show on both answerRatings after both are rated
//        return (!$this->nextAnswerAvailable && isset($this->rating)); //show on last answerRating after both are rated
    }
}
