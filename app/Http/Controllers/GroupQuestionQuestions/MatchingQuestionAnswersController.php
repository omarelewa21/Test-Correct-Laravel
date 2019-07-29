<?php namespace tcCore\Http\Controllers\GroupQuestionQuestions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\MatchingQuestionAnswer;
use tcCore\Http\Requests\CreateMatchingQuestionAnswerRequest;
use tcCore\Http\Requests\UpdateMatchingQuestionAnswerRequest;
use tcCore\MatchingQuestionAnswerLink;
use tcCore\QuestionAuthor;
use tcCore\GroupQuestionQuestion;

class MatchingQuestionAnswersController extends Controller {

	/**
	 * Display a listing of the matching question answers.
	 *
	 * @return Response
	 */
	public function index(GroupQuestionQuestionManager $groupQuestionQuestionManager, Request $request)
	{
		$groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			$matchingQuestionAnswers = $question->matchingQuestionAnswers()->filtered($request->get('filter', []), $request->get('order', []));

			switch(strtolower($request->get('mode', 'paginate'))) {
				case 'all':
					return Response::make($matchingQuestionAnswers->get(), 200);
					break;
				case 'list':
					return Response::make($matchingQuestionAnswers->get(['title'])->keyBy('id'), 200);
					break;
				case 'paginate':
				default:
					return Response::make($matchingQuestionAnswers->paginate(15), 200);
					break;
			}
		}
	}

	/**
	 * Store a newly created matching question answer in storage.
	 *
	 * @param CreateMatchingQuestionAnswerRequest $request
	 * @return Response
	 */
	public function store(GroupQuestionQuestionManager $groupQuestionQuestionManager, CreateMatchingQuestionAnswerRequest $request)
	{
	    $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			$matchingQuestionAnswer = new MatchingQuestionAnswer();

			$matchingQuestionAnswer->fill($request->only($matchingQuestionAnswer->getFillable()));

			if ($question->isUsed($groupQuestionQuestion)) {
				$question = $question->duplicate([]);
				if ($question === false) {
					return Response::make('Failed to duplicate question', 500);
				}

				$groupQuestionQuestion->setAttribute('question_id', $question->getKey());

				if (!$groupQuestionQuestion->save()) {
					return Response::make('Failed to update group question question', 500);
				}
			}

			if (!QuestionAuthor::addAuthorToQuestion($question)) {
				return Response::make('Failed to attach author to question', 500);
			}

			$matchingQuestionAnswer = new MatchingQuestionAnswer();

			$matchingQuestionAnswer->fill($request->only($matchingQuestionAnswer->getFillable()));
			if (!$matchingQuestionAnswer->save()) {
				return Response::make('Failed to create matching question answer', 500);
			}

			$matchingQuestionAnswerLink = new MatchingQuestionAnswerLink();
			$matchingQuestionAnswerLink->fill($request->only($matchingQuestionAnswerLink->getFillable()));
			$matchingQuestionAnswerLink->setAttribute('matching_question_id', $question->getKey());
			$matchingQuestionAnswerLink->setAttribute('matching_question_answer_id', $matchingQuestionAnswer->getKey());

			if($matchingQuestionAnswerLink->save()) {
				$matchingQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
				return Response::make($matchingQuestionAnswer, 200);
			} else {
				return Response::make('Failed to create matching question answer link', 500);
			}
		}
	}

	/**
	 * Display the specified matching question answer.
	 *
	 * @param  MatchingQuestionAnswer  $matchingQuestionAnswer
	 * @return Response
	 */
	public function show(GroupQuestionQuestionManager $groupQuestionQuestionManager, MatchingQuestionAnswer $matchingQuestionAnswer)
	{
		$groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($groupQuestionQuestion)) !== true) {
			return $response;
		} else {
			if (!$this->checkLinkExists($question, $matchingQuestionAnswer)) {
				return Response::make('matching question answer not found', 404);
			}

			return Response::make($matchingQuestionAnswer, 200);
		}
	}

	/**
	 * Update the specified matching question answer in storage.
	 *
	 * @param  MatchingQuestionAnswer $matchingQuestionAnswer
	 * @param UpdateMatchingQuestionAnswerRequest $request
	 * @return Response
	 */
	public function update(GroupQuestionQuestionManager $groupQuestionQuestionManager, MatchingQuestionAnswer $matchingQuestionAnswer, UpdateMatchingQuestionAnswerRequest $request)
	{
		$groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			$matchingQuestionAnswerLink = $question->matchingQuestionAnswers()->withTrashed()->where('matching_question_answer_id', $matchingQuestionAnswer->getKey())->first();
			if ($matchingQuestionAnswerLink === null) {
				return Response::make('matching question answer not found', 404);
			}

			$matchingQuestionAnswer->fill($request->all());
			$matchingQuestionAnswerLink->fill($request->all());

			if ($matchingQuestionAnswer->isDirty() || $matchingQuestionAnswerLink->isDirty()) {
				if (($questionDuplicated = $question->isUsed($groupQuestionQuestion))) {
					$question = $question->duplicate([], $matchingQuestionAnswer);
					if ($question === false) {
						return Response::make('Failed to duplicate question', 500);
					}

					if ($matchingQuestionAnswer->isDirty()) {
						$matchingQuestionAnswer = $matchingQuestionAnswer->duplicate($question, $request->all());
						if ($matchingQuestionAnswer === false) {
							return Response::make('Failed to duplicate and update matching question answer', 500);
						}
						$matchingQuestionAnswerLink->setAttribute('matching_question_answer_id', $matchingQuestionAnswer->getKey());
					}

					if (!QuestionAuthor::addAuthorToQuestion($question)) {
						return Response::make('Failed to attach author to question', 500);
					}

					$groupQuestionQuestion->setAttribute('question_id', $question->getKey());
					if ($questionDuplicated && !$groupQuestionQuestion->save()) {
						return Response::make('Failed to update group question question', 500);
					} else {
						$matchingQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
						return Response::make($matchingQuestionAnswer, 200);
					}
				} else {
					if ($matchingQuestionAnswer->isDirty()) {
						$matchingQuestionAnswer = $matchingQuestionAnswer->duplicate($question, $request->all());
						if ($matchingQuestionAnswer === false) {
							return Response::make('Failed to duplicate and update matching question answer', 500);
						}
						$matchingQuestionAnswerLink->setAttribute('matching_question_answer_id', $matchingQuestionAnswer->getKey());
					}

					if (!QuestionAuthor::addAuthorToQuestion($question)) {
						return Response::make('Failed to attach author to question', 500);
					}

					if ($question->matchingQuestionAnswers()->save($matchingQuestionAnswer) !== false) {
						$matchingQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
						return Response::make($matchingQuestionAnswer, 200);
					} else {
						return Response::make('Failed to update matching question answer', 500);
					}
				}
			} else {
				$matchingQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
				return Response::make($matchingQuestionAnswer, 200);
			}
		}
	}

	/**
	 * Remove the specified matching question answer from storage.
	 *
	 * @param  MatchingQuestionAnswer  $matchingQuestionAnswer
	 * @return Response
	 */
	public function destroy(GroupQuestionQuestionManager $groupQuestionQuestionManager, MatchingQuestionAnswer $matchingQuestionAnswer)
	{
		$groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			if (!$this->checkLinkExists($question, $matchingQuestionAnswer)) {
				return Response::make('Matching question answer not found', 404);
			}

			if ($question->isUsed($groupQuestionQuestion)) {
				$question = $question->duplicate([], $matchingQuestionAnswer);
				if ($question === false) {
					return Response::make('Failed to duplicate question', 500);
				}

				$groupQuestionQuestion->setAttribute('question_id', $question->getKey());
				if (!$groupQuestionQuestion->save()) {
					return Response::make('Failed to update group question question', 500);
				}
			}

			if (!QuestionAuthor::addAuthorToQuestion($question)) {
				return Response::make('Failed to attach author to question', 500);
			}

			$matchingQuestionAnswerLink = $question->matchingQuestionAnswers()->withTrashed()->where('matching_question_answer_id', $matchingQuestionAnswer->getKey())->first();
			if (!$matchingQuestionAnswerLink->delete()) {
				return Response::make('Failed to delete matching question answer link', 500);
			}

			if ($matchingQuestionAnswer->isUsed($matchingQuestionAnswerLink)) {
				$matchingQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
				return Response::make($matchingQuestionAnswer, 200);
			} else {
				if ($matchingQuestionAnswer->delete() ) {
					$matchingQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
					return Response::make($matchingQuestionAnswer, 200);
				} else {
					return Response::make('Failed to delete matching question answer', 500);
				}
			}
		}
	}

	public function destroyAll(GroupQuestionQuestionManager $groupQuestionQuestionManager) {
		$groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			if ($question->isUsed($groupQuestionQuestion)) {
				$question = $question->duplicate([], null);
				if ($question === false) {
					return Response::make('Failed to duplicate question', 500);
				}
			}

			if (!QuestionAuthor::addAuthorToQuestion($question)) {
				return Response::make('Failed to attach author to question', 500);
			}

			$matchingQuestionAnswerLinks = $question->matchingQuestionAnswerLinks()->with('matchingQuestionAnswer')->get();
			if ($matchingQuestionAnswerLinks->isEmpty()) {
				return Response::make(['group_question_question_path' => $groupQuestionQuestionManager->getGroupQuestionQuestionPath(), 'matching_question_answer_links' => []], 200);
			} elseif ($question->matchingQuestionAnswerLinks()->delete()) {
				foreach($matchingQuestionAnswerLinks as $matchingQuestionAnswerLink) {
					$matchingQuestionAnswer = $matchingQuestionAnswerLink->matchingQuestionAnswer;

					// if($matchingQuestionAnswer->isUsed($matchingQuestionAnswerLink) && !$matchingQuestionAnswer->delete()) {
					// 	Response::make('Failed to delete matching question answer', 500);
					// }
				}
				return Response::make(['group_question_question_path' => $groupQuestionQuestionManager->getGroupQuestionQuestionPath(), 'matching_question_answer_links' => $matchingQuestionAnswerLinks], 200);
			} else {
				return Response::make('Failed to delete matching question answers', 500);
			}
		}
	}

	/**
	 * Perform pre-action checks
	 * @param GroupQuestionQuestion $question
	 * @return bool
	 */
	protected function validateQuestion($question) {
		if (!method_exists($question, 'matchingQuestionAnswers')) {
			return Response::make('Question does not allow matching question answers.', 404);
		}

		return true;
	}

	protected function checkLinkExists($question, $matchingQuestionAnswer) {
		return ($question->matchingQuestionAnswers()->withTrashed()->where('matching_question_answer_id', $matchingQuestionAnswer->getKey())->count() > 0);
	}
}
