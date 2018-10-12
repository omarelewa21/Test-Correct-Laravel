<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateAnswerRequest extends Request {

	/**
	 * @var Answer
	 */
	private $answer;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->answer = $route->getParameter('answer');
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
			'test_participant_id' => '',
			'question_id' => '',
			'json' => ''
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
