<?php namespace tcCore\Http\Controllers\TestQuestions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\RankingQuestionAnswer;
use tcCore\Http\Requests\CreateRankingQuestionAnswerRequest;
use tcCore\Http\Requests\UpdateRankingQuestionAnswerRequest;
use tcCore\RankingQuestionAnswerLink;
use tcCore\QuestionAuthor;
use tcCore\TestQuestion;

class RankingQuestionAnswersController extends Controller {

	/**
	 * Display a listing of the ranking question answers.
	 *
	 * @return Response
	 */
	public function index(TestQuestion $testQuestion, Request $request)
	{
		$question = $testQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			$rankingQuestionAnswers = $question->rankingQuestionAnswers()->filtered($request->get('filter', []), $request->get('order', []));

			switch(strtolower($request->get('mode', 'paginate'))) {
				case 'all':
					return Response::make($rankingQuestionAnswers->get(), 200);
					break;
				case 'list':
					return Response::make($rankingQuestionAnswers->get(['title'])->keyBy('id'), 200);
					break;
				case 'paginate':
				default:
					return Response::make($rankingQuestionAnswers->paginate(15), 200);
					break;
			}
		}
	}

	/**
	 * Store a newly created ranking question answer in storage.
	 *
	 * @param CreateRankingQuestionAnswerRequest $request
	 * @return Response
	 */
	public function store(TestQuestion $testQuestion, CreateRankingQuestionAnswerRequest $request)
	{
		$question = $testQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			if ($question->isUsed($testQuestion)) {
				$question = $question->duplicate([]);
				if ($question === false) {
					return Response::make('Failed to duplicate question', 500);
				}

				$testQuestion->setAttribute('question_id', $question->getKey());

				if (!$testQuestion->save()) {
					return Response::make('Failed to update test question', 500);
				}
			}

			if (!QuestionAuthor::addAuthorToQuestion($question)) {
				return Response::make('Failed to attach author to question', 500);
			}

			$rankingQuestionAnswer = new RankingQuestionAnswer();

			$rankingQuestionAnswer->fill($request->only($rankingQuestionAnswer->getFillable()));
			if (!$rankingQuestionAnswer->save()) {
				return Response::make('Failed to create ranking question answer', 500);
			}

			$rankingQuestionAnswerLink = new RankingQuestionAnswerLink();
			$rankingQuestionAnswerLink->fill($request->only($rankingQuestionAnswerLink->getFillable()));
			$rankingQuestionAnswerLink->setAttribute('ranking_question_id', $question->getKey());
			$rankingQuestionAnswerLink->setAttribute('ranking_question_answer_id', $rankingQuestionAnswer->getKey());

			if($rankingQuestionAnswerLink->save()) {
				return Response::make($rankingQuestionAnswer, 200);
			} else {
				return Response::make('Failed to create ranking question answer', 500);
			}
		}
	}

	/**
	 * Display the specified ranking question answer.
	 *
	 * @param  RankingQuestionAnswer  $rankingQuestionAnswer
	 * @return Response
	 */
	public function show(TestQuestion $testQuestion, RankingQuestionAnswer $rankingQuestionAnswer)
	{
		$question = $testQuestion->question;
		if (($response = $this->validateQuestion($testQuestion)) !== true) {
			return $response;
		} else {
			if (!$this->checkLinkExists($question, $rankingQuestionAnswer)) {
				return Response::make('ranking question answer not found', 404);
			}

			return Response::make($rankingQuestionAnswer, 200);
		}
	}

	/**
	 * Update the specified ranking question answer in storage.
	 *
	 * @param  RankingQuestionAnswer $rankingQuestionAnswer
	 * @param UpdateRankingQuestionAnswerRequest $request
	 * @return Response
	 */
	public function update(TestQuestion $testQuestion, RankingQuestionAnswer $rankingQuestionAnswer, UpdateRankingQuestionAnswerRequest $request)
	{
		$question = $testQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			$rankingQuestionAnswerLink = $question->rankingQuestionAnswers()->withTrashed()->where('ranking_question_answer_id', $rankingQuestionAnswer->getKey())->first();
			if ($rankingQuestionAnswerLink === null) {
				return Response::make('ranking question answer not found', 404);
			}

			$rankingQuestionAnswer->fill($request->all());
			$rankingQuestionAnswerLink->fill($request->all());

			if ($rankingQuestionAnswer->isDirty() || $rankingQuestionAnswerLink->isDirty()) {
				if (($questionDuplicated = $question->isUsed($testQuestion))) {
					$question = $question->duplicate([], $rankingQuestionAnswer);
					if ($question === false) {
						return Response::make('Failed to duplicate question', 500);
					}

					if ($rankingQuestionAnswer->isDirty()) {
						$rankingQuestionAnswer = $rankingQuestionAnswer->duplicate($question, $request->all());
						if ($rankingQuestionAnswer === false) {
							return Response::make('Failed to duplicate and update ranking question answer', 500);
						}
						$rankingQuestionAnswerLink->setAttribute('ranking_question_answer_id', $rankingQuestionAnswer->getKey());
					}

					if (!QuestionAuthor::addAuthorToQuestion($question)) {
						return Response::make('Failed to attach author to question', 500);
					}

					$testQuestion->setAttribute('question_id', $question->getKey());
					if ($questionDuplicated && !$testQuestion->save()) {
						return Response::make('Failed to update test question', 500);
					} else {
						return Response::make($rankingQuestionAnswer, 200);
					}
				} else {
					if ($rankingQuestionAnswer->isDirty()) {
						$rankingQuestionAnswer = $rankingQuestionAnswer->duplicate($question, $request->all());
						if ($rankingQuestionAnswer === false) {
							return Response::make('Failed to duplicate and update ranking question answer', 500);
						}
						$rankingQuestionAnswerLink->setAttribute('ranking_question_answer_id', $rankingQuestionAnswer->getKey());
					}

					if (!QuestionAuthor::addAuthorToQuestion($question)) {
						return Response::make('Failed to attach author to question', 500);
					}

					if ($question->rankingQuestionAnswers()->save($rankingQuestionAnswer) !== false) {
						return Response::make($rankingQuestionAnswer, 200);
					} else {
						return Response::make('Failed to update ranking question answer', 500);
					}
				}
			} else {
				return Response::make($rankingQuestionAnswer, 200);
			}
		}
	}

	/**
	 * Remove the specified ranking question answer from storage.
	 *
	 * @param  RankingQuestionAnswer  $rankingQuestionAnswer
	 * @return Response
	 */
	public function destroy(TestQuestion $testQuestion, RankingQuestionAnswer $rankingQuestionAnswer)
	{
		$question = $testQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			if (!$this->checkLinkExists($question, $rankingQuestionAnswer)) {
				return Response::make('Ranking question answer not found', 404);
			}

			if ($question->isUsed($testQuestion)) {
				$question = $question->duplicate([], $rankingQuestionAnswer);
				if ($question === false) {
					return Response::make('Failed to duplicate question', 500);
				}

				$testQuestion->setAttribute('question_id', $question->getKey());
				if (!$testQuestion->save()) {
					return Response::make('Failed to update test question', 500);
				}
			}

			if (!QuestionAuthor::addAuthorToQuestion($question)) {
				return Response::make('Failed to attach author to question', 500);
			}

			$rankingQuestionAnswerLink = $question->rankingQuestionAnswers()->withTrashed()->where('ranking_question_answer_id', $rankingQuestionAnswer->getKey())->first();
			if (!$rankingQuestionAnswerLink->delete()) {
				return Response::make('Failed to delete ranking question answer link', 500);
			}

			if ($rankingQuestionAnswer->isUsed($rankingQuestionAnswerLink)) {
				return Response::make($rankingQuestionAnswer, 200);
			} else {
				if ($rankingQuestionAnswer->delete() ) {
					return Response::make($rankingQuestionAnswer, 200);
				} else {
					return Response::make('Failed to delete ranking question answer', 500);
				}
			}
		}
	}

	public function destroyAll(TestQuestion $testQuestion) {
		$question = $testQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			if ($question->isUsed($testQuestion)) {
				$question = $question->duplicate([], null);
				if ($question === false) {
					return Response::make('Failed to duplicate question', 500);
				}
			}

			if (!QuestionAuthor::addAuthorToQuestion($question)) {
				return Response::make('Failed to attach author to question', 500);
			}

			$rankingQuestionAnswerLinks = $question->rankingQuestionAnswerLinks()->with('rankingQuestionAnswer')->get();
			if ($rankingQuestionAnswerLinks->isEmpty()) {
				return Response::make([], 200);
			} elseif ($question->rankingQuestionAnswerLinks()->delete()) {
				// foreach($rankingQuestionAnswerLinks as $rankingQuestionAnswerLink) {
				// 	$rankingQuestionAnswer = $rankingQuestionAnswerLink->rankingQuestionAnswer;

				// 	if($rankingQuestionAnswer->isUsed($rankingQuestionAnswerLink) && !$rankingQuestionAnswer->delete()) {
				// 		Response::make('Failed to delete ranking question answer', 500);
				// 	}
				// }
				return Response::make($rankingQuestionAnswerLinks, 200);
			} else {
				return Response::make('Failed to delete ranking question answers', 500);
			}
		}
	}

	/**
	 * Perform pre-action checks
	 * @param TestQuestion $question
	 * @return bool
	 */
	protected function validateQuestion($question) {
		if (!method_exists($question, 'rankingQuestionAnswers')) {
			return Response::make('Question does not allow ranking question answers.', 404);
		}

		return true;
	}

	protected function checkLinkExists($question, $rankingQuestionAnswer) {
		return ($question->rankingQuestionAnswers()->withTrashed()->where('ranking_question_answer_id', $rankingQuestionAnswer->getKey())->count() > 0);
	}
}
