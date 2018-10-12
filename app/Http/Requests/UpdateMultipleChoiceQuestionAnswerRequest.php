<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateMultipleChoiceQuestionAnswerRequest extends Request {

	/**
	 * @var MultipleChoiceQuestionAnswer
	 */
	private $multipleChoiceQuestionAnswer;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->multipleChoiceQuestionAnswer = $route->getParameter('multiple_choice_question_answer');
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
		return [
			//
		];
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
