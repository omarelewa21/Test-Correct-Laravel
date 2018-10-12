<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateMatchingQuestionAnswerRequest extends Request {

	/**
	 * @var MatchingQuestionAnswer
	 */
	private $matchingQuestionAnswer;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->matchingQuestionAnswer = $route->getParameter('matching_question_answer');
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
			'correct_answer_id' => '',
			'answer' => '',
			'type' => '',
			'order' => ''
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
