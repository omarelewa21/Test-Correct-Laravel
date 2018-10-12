<?php namespace tcCore\Http\Controllers\TestQuestions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\Http\Requests\CreateMultipleChoiceQuestionAnswerRequest;
use tcCore\Http\Requests\UpdateMultipleChoiceQuestionAnswerRequest;
use tcCore\MultipleChoiceQuestionAnswerLink;
use tcCore\QuestionAuthor;
use tcCore\TestQuestion;

class MultipleChoiceQuestionAnswersController extends Controller {

	/**
	 * Display a listing of the multiple choice question answers.
	 *
	 * @return Response
	 */
	public function index(TestQuestion $testQuestion, Request $request)
	{
		$question = $testQuestion->question;
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
	public function store(TestQuestion $testQuestion, CreateMultipleChoiceQuestionAnswerRequest $request)
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
	public function show(TestQuestion $testQuestion, MultipleChoiceQuestionAnswer $multipleChoiceQuestionAnswer)
	{
		$question = $testQuestion->question;
		if (($response = $this->validateQuestion($testQuestion)) !== true) {
			return $response;
		} else {
			if (!$this->checkLinkExists($question, $multipleChoiceQuestionAnswer)) {
				return Response::make('multiple choice question answer not found', 404);
			}

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
	public function update(TestQuestion $testQuestion, MultipleChoiceQuestionAnswer $multipleChoiceQuestionAnswer, UpdateMultipleChoiceQuestionAnswerRequest $request)
	{
		$question = $testQuestion->question;
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
				if (($questionDuplicated = $question->isUsed($testQuestion))) {
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

					$testQuestion->setAttribute('question_id', $question->getKey());
					if ($questionDuplicated && !$testQuestion->save()) {
						return Response::make('Failed to update test question', 500);
					} else {
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
						return Response::make($multipleChoiceQuestionAnswer, 200);
					} else {
						return Response::make('Failed to update multiple choice question answer', 500);
					}
				}
			} else {
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
	public function destroy(TestQuestion $testQuestion, MultipleChoiceQuestionAnswer $multipleChoiceQuestionAnswer)
	{
		$question = $testQuestion->question;
		if (($response = $this->validateQuestion($question)) !== true) {
			return $response;
		} else {
			if (!$this->checkLinkExists($question, $multipleChoiceQuestionAnswer)) {
				return Response::make('Multiple choice question answer not found', 404);
			}

			if ($question->isUsed($testQuestion)) {
				$question = $question->duplicate([], $multipleChoiceQuestionAnswer);
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

			$multipleChoiceQuestionAnswerLink = $question->multipleChoiceQuestionAnswers()->withTrashed()->where('multiple_choice_question_answer_id', $multipleChoiceQuestionAnswer->getKey())->first();
			if (!$multipleChoiceQuestionAnswerLink->delete()) {
				return Response::make('Failed to delete multiple choice question answer link', 500);
			}

			if ($multipleChoiceQuestionAnswer->isUsed($multipleChoiceQuestionAnswerLink)) {
				return Response::make($multipleChoiceQuestionAnswer, 200);
			} else {
				if ($multipleChoiceQuestionAnswer->delete() ) {
					return Response::make($multipleChoiceQuestionAnswer, 200);
				} else {
					return Response::make('Failed to delete multiple choice question answer', 500);
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

			$multipleChoiceQuestionAnswerLinks = $question->multipleChoiceQuestionAnswerLinks()->with('multipleChoiceQuestionAnswer')->get();
			if ($multipleChoiceQuestionAnswerLinks->isEmpty()) {
				return Response::make([], 200);
			} elseif ($question->multipleChoiceQuestionAnswerLinks()->delete()) {
				foreach($multipleChoiceQuestionAnswerLinks as $multipleChoiceQuestionAnswerLink) {
					$multipleChoiceQuestionAnswer = $multipleChoiceQuestionAnswerLink->multipleChoiceQuestionAnswer;

					if(
						$multipleChoiceQuestionAnswer !== null 
						&& $multipleChoiceQuestionAnswer->isUsed($multipleChoiceQuestionAnswerLink) 
						// && !$multipleChoiceQuestionAnswer->delete()
					) {
						Response::make('Failed to delete multiple choice question answer', 500);
					}
				}
				return Response::make($multipleChoiceQuestionAnswerLinks, 200);
			} else {
				return Response::make('Failed to delete multiple choice question answers', 500);
			}
		}
	}

	/**
	 * Perform pre-action checks
	 * @param TestQuestion $question
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
