<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Response;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\TestTake;

class CoLearning extends Component
{
    public ?TestTake $testTake;
    public bool $nextAnswerAvailable;
    public ?int $rating = null;
    public int $maxRating;

    protected $answerRating = null;

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;
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

        $mode = 'first';
        $with = ['questions'];
        $filters = [
            "discussing_at_test_take_id" => $this->testTake->uuid,//"8287df51-f800-42c1-a9f6-77aace840eef",
            "rated"                      => "0",
        ];

        $answerRatings = AnswerRating::filtered($filters, [])->with('answer');

        if (is_array($with) && in_array('questions', $with)) {
            $answerRatings->with(['answer.question', 'answer.answerParentQuestions', 'answer.answerParentQuestions.groupQuestion']);
        } else {
            $answerRatings->with(['answer.question','answer.testparticipant']);
        }

        switch(strtolower($mode)) {
//            case 'all':
//                $answerRatings = $answerRatings->get();
//                if (is_array($request->get('with')) && in_array('questions', $request->get('with'))) {
//                    foreach ($answerRatings as $answerRating) {
//                        $answerRating->answer->question->loadRelated();
//                    }
//                }
//                return Response::make($answerRatings, 200);
//                break;
            case 'first':
                $answerRatingCount = $answerRatings->count();
                $answerRating = $answerRatings->first();

                if ($answerRating !== null) {
                    if ($answerRatingCount > 1) {
                        $answerRating->setAttribute('has_next', true);
                    } else {
                        $answerRating->setAttribute('has_next', false);
                    }

                    if (is_array($with) && in_array('questions', $with)) {
                        $answerRating->answer->question->loadRelated();
                    }
                    return $answerRating;
//                    return Response::make($answerRatings, 200);
                } else {
                    //todo ??
//                    return Response::make(['has_next' => false], 200);
                }
                break;
//            case 'list':
//                return Response::make($answerRatings->pluck('answer_id', 'id'), 200);
//                break;
//            case 'paginate':
//            default:
//                $answerRatings = $answerRatings->paginate(15);
//                if (is_array($request->get('with')) && in_array('questions', $request->get('with'))) {
//                    foreach ($answerRatings as $answerRating) {
//                        $answerRating->answer->question->loadRelated();
//                    }
//                }
//                return Response::make($answerRatings, 200);
//                break;
        }
    }
}
