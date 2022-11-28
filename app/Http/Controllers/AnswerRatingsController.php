<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Answer;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\AnswerRating;
use tcCore\Http\Requests\CreateAnswerRatingRequest;
use tcCore\Http\Requests\UpdateAnswerRatingRequest;
use tcCore\TestTake;
use tcCore\User;

class AnswerRatingsController extends Controller {

	/**
	 * Display a listing of the answer ratings.
	 *
	 * @return Response
	 */
	public function index(Requests\IndexAnswerRatingRequest $request)
	{
		return $this->indexGeneric($request);
	}

	public function indexFromWithin(Request $request)
	{
		return $this->indexGeneric($request);
	}

    public function indexGeneric($request)
    {
        $answerRatings = AnswerRating::filtered($request->get('filter', []), $request->get('order', []))->with('answer');
        if (is_array($request->get('with')) && in_array('questions', $request->get('with'))) {
            $answerRatings->with(['answer.question', 'answer.answerParentQuestions', 'answer.answerParentQuestions.groupQuestion']);
        } else {
            $answerRatings->with(['answer.question','answer.testparticipant']);
        }

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                $answerRatings = $answerRatings->get();
                if (is_array($request->get('with')) && in_array('questions', $request->get('with'))) {
                    foreach ($answerRatings as $answerRating) {
                        $answerRating->answer->question->loadRelated();
                    }
                }
                return Response::make($answerRatings, 200);
                break;
            case 'first':
                $answerRatingCount = $answerRatings->count();
                $answerRatings = $answerRatings->first();

                if ($answerRatings !== null) {
                    if ($answerRatingCount > 1) {
                        $answerRatings->setAttribute('has_next', true);
                    } else {
                        $answerRatings->setAttribute('has_next', false);
                    }

                    if (is_array($request->get('with')) && in_array('questions', $request->get('with'))) {
                        $answerRatings->answer->question->loadRelated();
                    }
                    return Response::make($answerRatings, 200);
                } else {
                    return Response::make(['has_next' => false], 200);
                }
                break;
            case 'list':
                return Response::make($answerRatings->pluck('answer_id', 'id'), 200);
                break;
            case 'paginate':
            default:
                $answerRatings = $answerRatings->paginate(15);
                if (is_array($request->get('with')) && in_array('questions', $request->get('with'))) {
                    foreach ($answerRatings as $answerRating) {
                        $answerRating->answer->question->loadRelated();
                    }
                }
                return Response::make($answerRatings, 200);
                break;
        }
    }

	/**
	 * Store a newly created answer rating in storage.
	 *
	 * @param CreateAnswerRatingRequest $request
	 * @return Response
	 */
	public function store(CreateAnswerRatingRequest $request)
	{
		//
		$answerRating = new AnswerRating($request->all());
		if ($answerRating->save()) {
			return Response::make($answerRating, 200);
		} else {
			return Response::make('Failed to create answer rating', 500);
		}
	}

	/**
	 * Display the specified answer rating.
	 *
	 * @param  AnswerRating  $answerRating
	 * @return Response
	 */
	public function show(AnswerRating $answerRating)
	{
		//
		$answerRating->load('answer');
		return Response::make($answerRating, 200);
	}

	/**
	 * Update the specified answer rating in storage.
	 *
	 * @param  AnswerRating $answerRating
	 * @param UpdateAnswerRatingRequest $request
	 * @return Response
	 */
	public function update(AnswerRating $answerRating, UpdateAnswerRatingRequest $request)
	{
		if ($answerRating->update($request->all())) {
			return Response::make($answerRating, 200);
		} else {
			return Response::make('Failed to update answer rating', 500);
		}
	}

	/**
	 * Remove the specified answer rating from storage.
	 *
	 * @param  AnswerRating  $answerRating
	 * @return Response
	 */
	public function destroy(AnswerRating $answerRating)
	{
		//
		if ($answerRating->delete()) {
			return Response::make($answerRating, 200);
		} else {
			return Response::make('Failed to delete answer rating', 500);
		}
	}

}
