<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\Http\Controllers\AnswerRatingsController;
use tcCore\Http\Controllers\TestTakeLaravelController;
use tcCore\TestTake;

class CoLearning extends Component
{
    public ?TestTake $testTake;
    public bool $nextAnswerAvailable = false;
    public ?int $rating = null;
    public int $maxRating;

    public string $testName = 'test;';

    protected $answerRating = null;
    public $answerRatingId;

    public int $numberOfQuestions;
    public int $questionFollowUpNumber;
    public int $numberOfAnswers;
    public int $answerFollowUpNumber;

    protected function getListeners()
    {
        return [
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.question' => 'getNextAnswerRating', //.send, the dot is necessary
            'UpdateAnswerRating'                                                          => 'updateAnswerRating'
        ];
    }

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;
        $this->testParticipant = $test_take->testParticipants->where('user_id', auth()->id())->first();

        $this->getNextAnswerRating();

        $this->getQuestionAnsAnswerNavigationData();

    }

    public function render()
    {
        if (is_null($this->answerRating)) {
            $this->answerRating = AnswerRating::find($this->answerRatingId);
        }

        //todo remove temp:
        $this->maxRating = 8;

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

        // This is how answerRatings are updated when a student clicks on save rating in the old CO-learning
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
        return $this->render();
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
        $this->answerRatingId = $this->answerRating->getKey();
        $this->nextAnswerAvailable = (bool)optional($this->answerRating)->has_next ?: false;
        $this->maxRating = $this->answerRating->answer->question->score;
    }

    protected function getQuestionAnsAnswerNavigationData()
    {
        $testTakeQuestionsCollection = TestTakeLaravelController::getData(null, $this->testTake);
        $currentQuestionId = $this->answerRating->answer->question->getKey();

        $this->numberOfQuestions = $testTakeQuestionsCollection->count();

        $testTakeQuestionsCollection->reduce(function($carry, $question) use ($currentQuestionId) {
            $carry++;
            if ($question->id == $currentQuestionId){
                $this->questionFollowUpNumber = $carry;
            }
            return $carry;
        }, 0);

        $answersForUserAndCurrentQuestion = AnswerRating::filtered(['user_id' => auth()->user()->uuid])->get()
            ->map->answer
            ->filter(function($answer) use ($currentQuestionId) {
                return $answer->question->id == $currentQuestionId;
            });

        $this->numberOfAnswers = $answersForUserAndCurrentQuestion->count();

        $answersForUserAndCurrentQuestion->reduce(function($carry, $answer) {
            $carry++;
            if ($answer->id == $this->answerRating->answer->id){
                $this->answerFollowUpNumber = $carry;
            }
            return $carry;
        }, 0);
    }

}
