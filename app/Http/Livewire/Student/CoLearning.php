<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\Http\Controllers\AnswerRatingsController;
use tcCore\TestTake;

class CoLearning extends Component
{
    public ?TestTake $testTake;
    public bool $nextAnswerAvailable;
    public ?int $rating = null;
    public int $maxRating;

    protected $answerRating = null;

    protected function getListeners()
    {
        return [
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.question' => 'dd', //.send, the dot is necessary
        ];
    }

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;
        $this->testParticipant = $test_take->testParticipants->where('user_id', auth()->id())->first();
    }

    public function render()
    {
        $this->answerRating = $this->getNextAnswerRating();
        $this->nextAnswerAvailable = (bool) optional($this->answerRating)->has_next;

        $this->maxRating = $this->answerRating->answer->question->score;

        return view('livewire.student.co-learning');
//            ->layout('layouts.student');
    }

    //todo implement or remove
    public function updateAnswerRating()
    {
//        if(!$this->answerRating instanceof AnswerRating){
//            $this->answerRating = AnswerRating::find($this->answerRating);
//        }
        if($this->rating < 0 || $this->rating > $this->maxRating){
            throw new \Exception('Supplied rating is out of bounds.');
        }

        // This is how answerRatings are updated when a student clicks on save rating in the old CO-learning
        $this->answerRating->update(['rating' => $this->rating]);
    }

    public function getNextAnswerRating(){
        if($this->answerRating instanceof AnswerRating && !$this->answerRating->has_next)
        {
            //todo implement notification to student that there is no next question, until the teacher continues.
            return $this->dispatchBrowserEvent('notify', ['message' => 'hello']);
        }

        $this->answerRating = null;

        $params = [
            'mode' => 'first',
            'with' => ['questions'],
            'filter' => [
                "discussing_at_test_take_id" => $this->testTake->uuid,//"8287df51-f800-42c1-a9f6-77aace840eef",
                "rated"                      => "0",
            ],
        ];

        $request = new Request();
        $request->merge($params);

        $response = (new AnswerRatingsController())->indexFromWithin($request);
        return $response->getOriginalContent();
    }
}
