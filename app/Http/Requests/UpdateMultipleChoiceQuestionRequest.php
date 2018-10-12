<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\MultipleChoiceQuestion;

class UpdateMultipleChoiceQuestionRequest extends UpdateQuestionRequest {

	/**
	 * @var MultipleChoiceQuestion
	 */
	private $multipleChoiceQuestion;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->multipleChoiceQuestion = $route->getParameter('multiple_choice_question');
		if ($this->multipleChoiceQuestion instanceof MultipleChoiceQuestion) {
			$this->question = $this->multipleChoiceQuestion->getQuestionInstance();
		}
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$baseRules = parent::baseRules();

		return array_merge($baseRules, [
			'type' => 'sometimes|required|in:MultipleChoiceQuestion',
			'subtype' => 'sometimes|required',
			'selectable_answers' => 'sometimes|numeric|min:1'
		]);
	}

	/**
	 * Get the sanitized input for the request.
	 *
	 * @return array
	 */
	public function sanitize()
	{
		return $this->all();
	}

}
