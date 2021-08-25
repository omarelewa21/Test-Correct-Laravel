<?php namespace tcCore\Http\Controllers\TestParticipants;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Answer;
use tcCore\Http\Requests\CreateAnswerRequest;
use tcCore\Http\Requests\UpdateAnswerRequest;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\TestParticipant;

class AnswersController extends Controller {

	/**
	 * Display a listing of the answers.
	 *
	 * @return Response
	 */
	public function index(TestParticipant $testParticipant, Request $request)
	{
		$answers = $testParticipant->answers()->orderBy('order')->with('answerParentQuestions');

		(new Answer())->scopeFiltered($answers, $request->get('filter', []), $request->get('order', []));

		if (is_array($request->get('with')) && in_array('answer_ratings', $request->get('with'))) {
			$answers->with('answerRatings', 'answerRatings.user', 'Question', 'answerParentQuestions', 'answerParentQuestions.groupQuestion', 'answerParentQuestions.groupQuestion.attachments');
		} elseif (is_array($request->get('with')) && in_array('question', $request->get('with'))) {
			$answers->with('Question', 'answerParentQuestions', 'answerParentQuestions.groupQuestion', 'answerParentQuestions.groupQuestion.attachments');
		}

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				$answers = $answers->get();
				if (is_array($request->get('with')) && in_array('answer_ratings', $request->get('with'))) {
					foreach ($answers as $answer) {
						if ($answer->question instanceof QuestionInterface) {
							$answer->question->loadRelated();
						}
					}
				}
				break;
			case 'paginate':
			default:
				$answers = $answers->paginate(15);
				if (is_array($request->get('with')) && in_array('answer_ratings', $request->get('with'))) {
					foreach ($answers as $answer) {
						if ($answer->question instanceof QuestionInterface) {
							$answer->question->loadRelated();
						}
					}
				}
				break;
		}

//		foreach($answers as $answer){
//			$answer->question->transformIfNeededForTest();
//		}
		return Response::make($answers, 200);
	}

	/**
	 * Store a newly created answer in storage.
	 *
	 * @param CreateAnswerRequest $request
	 * @return Response
	 */
	public function store(TestParticipant $testParticipant, CreateAnswerRequest $request)
	{
		$answer = new Answer();

		$answer->fill($request->all());

		if ($testParticipant->answers()->save($answer) !== false) {
			return Response::make($answer, 200);
		} else {
			return Response::make('Failed to create answer', 500);
		}
	}

	/**
	 * Display the specified answer.
	 *
	 * @param  Answer  $answer
	 * @return Response
	 */
	public function show(TestParticipant $testParticipant, Answer $answer)
	{
		$answer->load('answerParentQuestions');
		if ($answer->test_participant_id !== $testParticipant->getKey()) {
			return Response::make('Answer not found', 404);
		} else {
			return Response::make($answer, 200);
		}
	}

	/**
	 * Update the specified answer in storage.
	 *
	 * @param  Answer $answer
	 * @param UpdateAnswerRequest $request
	 * @return Response
	 */
	public function update(TestParticipant $testParticipant, Answer $answer, UpdateAnswerRequest $request)
	{
		$answer->fill($request->all());

		if ($testParticipant->answers()->save($answer) !== false) {
			return Response::make($answer, 200);
		} else {
			return Response::make('Failed to update answer', 500);
		}
	}

	/**
	 * Remove the specified answer from storage.
	 *
	 * @param  Answer  $answer
	 * @return Response
	 */
	public function destroy(TestParticipant $testParticipant, Answer $answer)
	{
		if ($answer->test_participant_id !== $testParticipant->getKey()) {
			return Response::make('Answer not found', 404);
		}

		if ($answer->delete()) {
			return Response::make($answer, 200);
		} else {
			return Response::make('Failed to delete answer', 500);
		}
	}

    public function getDrawingAnswerUrl(Answer $answer)
    {
        $url['url'] = config('app.url_login').'test_takes/'.$answer->getDrawingStoragePath().'?'.date('ymds');
        if (request('base64')) {
            $url['url'] = Storage::get($answer->getDrawingStoragePath());
        }
        return Response::make($url, 200);
	}

}
