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
    public ?int $rating = null;
    public int $maxRating;

    public bool $informationScreenQuestion = false;
    public bool $studentFinishedScreen = false;

    public string $testName = 'test;';

    protected $answerRating = null;
    public $answerRatingId;

    public int $numberOfQuestions;
    public int $questionFollowUpNumber = 0;
    public int $numberOfAnswers;
    public int $answerFollowUpNumber = 0;

    /**
     * @return void
     */
    public function getLastUpdatedAnswerRatingForUser(): bool
    {
        $this->answerRating = AnswerRating::filtered([
            'user_id'                    => auth()->user()->uuid,
            "discussing_at_test_take_id" => $this->testTake->uuid
        ])
            ->orderByDesc('updated_at')
            ->first();
        if (!$this->answerRating instanceof AnswerRating) {
            return false;
        }
        if (!is_null($this->answerRating->rating)) {
            $this->rating = $this->answerRating->rating;
        }
        return true;
    }

    protected function getListeners()
    {
        return [
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.CoLearningNextQuestion'   => 'getNextAnswerRating',
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.CoLearningForceTakenAway' => 'redirectToTestTakesInReview', //.action, the dot is necessary
            'UpdateAnswerRating'                                                                          => 'updateAnswerRating'
        ];
    }

    public function mount(TestTake $test_take)
    {
        if ($test_take->test_take_status_id !== 7) {
            return redirect()->route('student.test-takes', ['tab' => 'discuss']);
        }
        $this->testTake = $test_take;
        $this->testParticipant = $test_take->testParticipants->where('user_id', auth()->id())->first();

        if ($this->getNextAnswerRating()) {
            $this->getQuestionAnsAnswerNavigationData();
        }

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
        $this->updateAnswerRating();
    }

    //todo implement or remove
    public function updateAnswerRating()
    {
        if ($this->rating < 0 || $this->rating > $this->maxRating) {
            throw new \Exception('Supplied rating is out of bounds.');
        }
        $this->answerRating = AnswerRating::find($this->answerRatingId);

        $this->answerRating->update(['rating' => $this->rating]);
    }

    public function sendWaitForTeacherNotification()
    {
        return $this->dispatchBrowserEvent('notify', ['message' => __('co-learning.wait_for_teacher'), 'type' => 'error']);
    }

    public function goToNextAnswerRating()
    {
        if (!$this->nextAnswerAvailable) {
            return $this->sendWaitForTeacherNotification();
        }
        $this->getNextAnswerRating();
    }

    public function getNextAnswerRating()
    {
        $params = [
            'mode'   => 'first',
            'with'   => ['questions'],
            'filter' => [
                "discussing_at_test_take_id" => $this->testTake->uuid,//"8287df51-f800-42c1-a9f6-77aace840eef",
                "rated"                      => "0",
            ],
        ];

        $request = new Request();
        $request->merge($params);

        $response = (new AnswerRatingsController())->indexFromWithin($request);
        $this->answerRating = $response->getOriginalContent();

        if (!$this->answerRating instanceof AnswerRating) {
            if ($this->testTake->discussingQuestion->type === 'InfoscreenQuestion') {
                $this->informationScreenQuestion = true;
                $this->getQuestionAnsAnswerNavigationData();
                return false;
            }
            if (!$this->getLastUpdatedAnswerRatingForUser()) {
                $this->studentFinishedScreen = true;
                return false;
            }
        }

        $this->answerRatingId = $this->answerRating->getKey();
        $this->nextAnswerAvailable = (bool)optional($this->answerRating)->has_next ?: false;
        $this->maxRating = $this->answerRating->answer->question->score;
        $this->answerRating->refresh();

        $this->getQuestionAnsAnswerNavigationData();

        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);

    }

    protected function getQuestionAnsAnswerNavigationData()
    {
        $testTakeQuestionsCollection = TestTakeLaravelController::getData(null, $this->testTake);
        $currentQuestionId = $this->testTake->discussingQuestion->getKey();

        $this->numberOfQuestions = $testTakeQuestionsCollection->count();

        $testTakeQuestionsCollection->reduce(function ($carry, $question) use ($currentQuestionId) {
            $carry++;
            if ($question->id == $currentQuestionId) {
                $this->questionFollowUpNumber = $carry;
            }
            return $carry;
        }, 0);

        $answersForUserAndCurrentQuestion = AnswerRating::filtered(['user_id' => auth()->user()->uuid])->get()
            ->filter(function ($answerRating) use ($currentQuestionId) {
                return $answerRating->answer->question->id == $currentQuestionId && $answerRating->test_take_id == $this->testTake->id;
            })->map->answer;

        $this->numberOfAnswers = $answersForUserAndCurrentQuestion->count();

        if($this->numberOfAnswers != 0){
            $answersForUserAndCurrentQuestion->reduce(function ($carry, $answer) {
                $carry++;
                if ($answer->id == $this->answerRating->answer->id) {
                    $this->answerFollowUpNumber = $carry;
                }
                return $carry;
            }, 0);
        }

    }

    public function redirectToTestTakesInReview()
    {
        return redirect()->route('student.test-takes', ['tab' => 'review']);
    }

    public function getCannotViewFooterInformationProperty()
    {
        return ($this->informationScreenQuestion || $this->studentFinishedScreen);
    }
}
