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
    public ?int $rating = null;
    public int $maxRating;

    public bool $informationScreenQuestion = false;
    public bool $studentWaitForTeacher = false;

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
        if(isset($this->answerRatingId)){
            if($navigateDirection == 'next' && $this->nextAnswerAvailable) {
                $this->answerRating = $this->answerRatings->filter(fn($ar) => $ar->getKey() > $this->answerRatingId)->first();
                $this->answerRatingId = $this->answerRating->getKey();
                return;
            }
            if($navigateDirection == 'previous' && $this->previousAnswerAvailable) {
                $this->answerRating = $this->answerRatings->filter(fn($ar) => $ar->getKey() < $this->answerRatingId)->last();
                $this->answerRatingId = $this->answerRating->getKey();
                return;
            }

            if(isset($this->answerRatingId) && $this->answerRatings->map->id->contains($this->answerRatingId)){
                $this->answerRating = $this->answerRatings->where('id', $this->answerRatingId)->first();
                return;
            }
        }
        $this->answerRating = $this->answerRatings->first();
        $this->answerRatingId = $this->answerRating->getKey();


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

        $this->getAnswerRatings();
    }

    public function render()
    {
        if (is_null($this->answerRating)) {
            $this->answerRating = AnswerRating::find($this->answerRatingId);
        }

        $this->studentWaitForTeacher = $this->shouldShowWaitForTeacherNotification();

        return view('livewire.student.co-learning')
            ->layout('layouts.co-learning');
    }

    public function updatedRating()
    {
        $this->updateAnswerRating();
    }

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


    /**
     * TODO
     *  in mount get answerRatings, set Active AnswerRating
     *  then, introduce two methods to navigate between answerRatings
     *  * nextAnswerRating()
     *  * previousAnswerRating()
     */


    public function goToPreviousAnswerRating()
    {
        if(!$this->previousAnswerAvailable){
            return;
        }
        $this->getAnswerRatings('previous');

        //todo emit to livewire component to refresh data
        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);
    }

    public function goToNextAnswerRating()
    {
        if(!$this->nextAnswerAvailable){
            return $this->sendWaitForTeacherNotification();
        }

        $this->getAnswerRatings('next');

        $this->emit('getNextAnswerRating', [$this->answerRatingId, $this->questionFollowUpNumber, $this->answerFollowUpNumber]);
    }

    protected function getQuestionAndAnswerNavigationData()
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
        //todo (check if) set answerFollowUpNumber to 1 if nothing found? (when at infoscreenQuestion)
    }

    protected function getAnswerRatings($navigateDirection = null)
    {
        $params = [
            'mode'   => 'all',
            'with'   => ['questions'],
            'filter' => [
                "discussing_at_test_take_id" => $this->testTake->uuid,//"8287df51-f800-42c1-a9f6-77aace840eef",
//                "rated"                      => "0",
            ],
            'order' => ['id' => 'asc'] //make sure order of answerRatings is allways the same
        ];

        $request = new Request();
        $request->merge($params);

        $response = (new AnswerRatingsController())->indexFromWithin($request);
        $this->answerRatings = $response->getOriginalContent()->keyBy('id');
        //    386 => tcCore\AnswerRating {#2539 ▶}
        //    391 => tcCore\AnswerRating {#2574 ▶}


        $this->setActiveAnswerRating($navigateDirection);
        $this->rating = $this->answerRating->rating;

        $this->previousAnswerAvailable = $this->answerRatings->filter(fn($ar) => $ar->getKey() < $this->answerRatingId)->count() > 0;
        $this->nextAnswerAvailable = $this->answerRatings->filter(fn($ar) => $ar->getKey() > $this->answerRatingId)->count() > 0;

        $this->getQuestionAndAnswerNavigationData();

        if (!$this->answerRating instanceof AnswerRating) {
            if ($this->testTake->discussingQuestion->type === 'InfoscreenQuestion') {
                $this->informationScreenQuestion = true;
                return false;
            }
        }
        $this->maxRating = $this->answerRating->answer->question->score;
        $this->answerRating->refresh();
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
//        return !$this->answerRatings->map->rating->contains(null); //show on both answerRatings after both are rated

        return (!$this->nextAnswerAvailable && isset($this->rating)); //show on last answerRating after both are rated
    }
}
