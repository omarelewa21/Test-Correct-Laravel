<?php namespace tcCore\Http\Controllers\GroupQuestionQuestions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\Http\Requests\CreateMultipleChoiceQuestionAnswerRequest;
use tcCore\Http\Requests\UpdateMultipleChoiceQuestionAnswerRequest;
use tcCore\MultipleChoiceQuestionAnswerLink;
use tcCore\QuestionAuthor;
use tcCore\GroupQuestionQuestion;

class MultipleChoiceQuestionAnswersController extends Controller {

	/**
	 * Display a listing of the multiple choice question answers.
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
			$multipleChoiceQuestionAnswers = $question->multipleChoiceQuestionAnswers()->filtered($request->get('filter', []), $request->get('order', []));

			switch(strtolower($request->get('mode', 'paginate'))) {
				case 'all':
					return Response::make($multipleChoiceQuestionAnswers->get(), 200);
					break;
				case 'list':
					return Response::make($multipleChoiceQuestionAnswers->get(['title'])->keyBy('id'), 200);
					break;
				case 'paginate':
				default:
					return Response::make($multipleChoiceQuestionAnswers->paginate(15), 200);
					break;
			}
		}
	}

	/**
	 * Store a newly created multiple choice question answer in storage.
	 *
	 * @param CreateMultipleChoiceQuestionAnswerRequest $request
	 * @return Response
	 */
	public function store(GroupQuestionQuestionManager $groupQuestionQuestionManager, CreateMultipleChoiceQuestionAnswerRequest $request)
	{
		$groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
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

			$multipleChoiceQuestionAnswer = new MultiplechoiceQuestionAnswer();

			$multipleChoiceQuestionAnswer->fill($request->only($multipleChoiceQuestionAnswer->getFillable()));
			if (!$multipleChoiceQuestionAnswer->save()) {
				return Response::make('Failed to create multiplechoice question answer', 500);
			}

			$multipleChoiceQuestionAnswerLink = new MultiplechoiceQuestionAnswerLink();
			$multipleChoiceQuestionAnswerLink->fill($request->only($multipleChoiceQuestionAnswerLink->getFillable()));
			$multipleChoiceQuestionAnswerLink->setAttribute('multiple_choice_question_id', $question->getKey());
			$multipleChoiceQuestionAnswerLink->setAttribute('multiple_choice_question_answer_id', $multipleChoiceQuestionAnswer->getKey());

			if($multipleChoiceQuestionAnswerLink->save()) {
				$multipleChoiceQuestionAnswerLink->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
				return Response::make($multipleChoiceQuestionAnswerLink, 200);
			} else {
				return Response::make('Failed to create multiple choice question answer link', 500);
			}
		}
	}

	/**
	 * Display the specified multiple choice question answer.
	 *
	 * @param  MultipleChoiceQuestionAnswer  $multipleChoiceQuestionAnswer
	 * @return Response
	 */
	public function show(GroupQuestionQuestionManager $groupQuestionQuestionManager, MultipleChoiceQuestionAnswer $multipleChoiceQuestionAnswer)
	{
		$groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($groupQuestionQuestion)) !== true) {
			return $response;
		} else {
			if (!$this->checkLinkExists($question, $multipleChoiceQuestionAnswer)) {
				return Response::make('multiple choice question answer not found', 404);
			}

			$multipleChoiceQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
			return Response::make($multipleChoiceQuestionAnswer, 200);
		}
	}

	/**
	 * Update the specified multiple choice question answer in storage.
	 *
	 * @param  MultipleChoiceQuestionAnswer $multipleChoiceQuestionAnswer
	 * @param UpdateMultipleChoiceQuestionAnswerRequest $request
	 * @return Response
	 */
	public function update(GroupQuestionQuestionManager $groupQuestionQuestionManager, MultipleChoiceQuestionAnswer $multipleChoiceQuestionAnswer, UpdateMultipleChoiceQuestionAnswerRequest $request)
	{
		$groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			$multipleChoiceQuestionAnswerLink = $question->multipleChoiceQuestionAnswers()->withTrashed()->where('multiple_choice_question_answer_id', $multipleChoiceQuestionAnswer->getKey())->first();
			if ($multipleChoiceQuestionAnswerLink === null) {
				return Response::make('multiple choice question answer not found', 404);
			}

			$multipleChoiceQuestionAnswer->fill($request->all());
			$multipleChoiceQuestionAnswerLink->fill($request->all());

			if ($multipleChoiceQuestionAnswer->isDirty() || $multipleChoiceQuestionAnswerLink->isDirty()) {
				if (($questionDuplicated = $question->isUsed($groupQuestionQuestion))) {
					$question = $question->duplicate([], $multipleChoiceQuestionAnswer);
					if ($question === false) {
						return Response::make('Failed to duplicate question', 500);
					}

					if ($multipleChoiceQuestionAnswer->isDirty()) {
						$multipleChoiceQuestionAnswer = $multipleChoiceQuestionAnswer->duplicate($question, $request->all());
						if ($multipleChoiceQuestionAnswer === false) {
							return Response::make('Failed to duplicate and update multiple choice question answer', 500);
						}
						$multipleChoiceQuestionAnswerLink->setAttribute('multiple_choice_question_answer_id', $multipleChoiceQuestionAnswer->getKey());
					}

					if (!QuestionAuthor::addAuthorToQuestion($question)) {
						return Response::make('Failed to attach author to question', 500);
					}

					$groupQuestionQuestion->setAttribute('question_id', $question->getKey());
					if ($questionDuplicated && !$groupQuestionQuestion->save()) {
						return Response::make('Failed to update group question question', 500);
					} else {
						$multipleChoiceQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
						return Response::make($multipleChoiceQuestionAnswer, 200);
					}
				} else {
					if ($multipleChoiceQuestionAnswer->isDirty()) {
						$multipleChoiceQuestionAnswer = $multipleChoiceQuestionAnswer->duplicate($question, $request->all());
						if ($multipleChoiceQuestionAnswer === false) {
							return Response::make('Failed to duplicate and update multiple choice question answer', 500);
						}
						$multipleChoiceQuestionAnswerLink->setAttribute('multiple_choice_question_answer_id', $multipleChoiceQuestionAnswer->getKey());
					}

					if (!QuestionAuthor::addAuthorToQuestion($question)) {
						return Response::make('Failed to attach author to question', 500);
					}

					if ($question->multipleChoiceQuestionAnswers()->save($multipleChoiceQuestionAnswer) !== false) {
						$multipleChoiceQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
						return Response::make($multipleChoiceQuestionAnswer, 200);
					} else {
						return Response::make('Failed to update multiple choice question answer', 500);
					}
				}
			} else {
				$multipleChoiceQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
				return Response::make($multipleChoiceQuestionAnswer, 200);
			}
		}
	}

	/**
	 * Remove the specified multiple choice question answer from storage.
	 *
	 * @param  MultipleChoiceQuestionAnswer  $multipleChoiceQuestionAnswer
	 * @return Response
	 */
	public function destroy(GroupQuestionQuestionManager $groupQuestionQuestionManager, MultipleChoiceQuestionAnswer $multipleChoiceQuestionAnswer)
	{
		$groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
		$question = $groupQuestionQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			if (!$this->checkLinkExists($question, $multipleChoiceQuestionAnswer)) {
				return Response::make('Multiple choice question answer not found', 404);
			}

			if ($question->isUsed($groupQuestionQuestion)) {
				$question = $question->duplicate([], $multipleChoiceQuestionAnswer);
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

			$multipleChoiceQuestionAnswerLink = $question->multipleChoiceQuestionAnswers()->withTrashed()->where('multiple_choice_question_answer_id', $multipleChoiceQuestionAnswer->getKey())->first();
			if (!$multipleChoiceQuestionAnswerLink->delete()) {
				return Response::make('Failed to delete multiple choice question answer link', 500);
			}

			if ($multipleChoiceQuestionAnswer->isUsed($multipleChoiceQuestionAnswerLink)) {
				$multipleChoiceQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
				return Response::make($multipleChoiceQuestionAnswer, 200);
			} else {
				if ($multipleChoiceQuestionAnswer->delete() ) {
					$multipleChoiceQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
					return Response::make($multipleChoiceQuestionAnswer, 200);
				} else {
					return Response::make('Failed to delete multiple choice question answer', 500);
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

			$multipleChoiceQuestionAnswerLinks = $question->multipleChoiceQuestionAnswerLinks()->with('multipleChoiceQuestionAnswer')->get();
			if ($multipleChoiceQuestionAnswerLinks->isEmpty()) {
				return Response::make(['group_question_question_path' => $groupQuestionQuestionManager->getGroupQuestionQuestionPath(), 'multiple_choice_question_answer_links' => []], 200);
			} elseif ($question->multipleChoiceQuestionAnswerLinks()->delete()) {
				foreach($multipleChoiceQuestionAnswerLinks as $multipleChoiceQuestionAnswerLink) {
					$multipleChoiceQuestionAnswer = $multipleChoiceQuestionAnswerLink->multipleChoiceQuestionAnswer;

					// if($multipleChoiceQuestionAnswer->isUsed($multipleChoiceQuestionAnswerLink) && !$multipleChoiceQuestionAnswer->delete()) {
					// 	Response::make('Failed to delete multiple choice question answer', 500);
					// }
				}
				return Response::make(['group_question_question_path' => $groupQuestionQuestionManager->getGroupQuestionQuestionPath(), 'multiple_choice_question_answer_links' => $multipleChoiceQuestionAnswerLinks], 200);
			} else {
				return Response::make('Failed to delete multiple choice question answers', 500);
			}
		}
	}

	/**
	 * Perform pre-action checks
	 * @param GroupQuestionQuestion $question
	 * @return bool
	 */
	protected function validateQuestion($question) {
		if (!method_exists($question, 'multipleChoiceQuestionAnswers')) {
			return Response::make('Question does not allow multiple choice question answers.', 404);
		}

		return true;
	}

	protected function checkLinkExists($question, $multipleChoiceQuestionAnswer) {
		return ($question->multipleChoiceQuestionAnswers()->withTrashed()->where('multiple_choice_question_answer_id', $multipleChoiceQuestionAnswer->getKey())->count() > 0);
	}
}
