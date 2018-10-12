<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\OpenQuestion;

class UpdateOpenQuestionRequest extends UpdateQuestionRequest {

	/**
	 * @var OpenQuestion
	 */
	private $openQuestion;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->openQuestion = $route->getParameter('open_question');
		if ($this->openQuestion instanceof OpenQuestion) {
			$this->question = $this->openQuestion->getQuestionInstance();
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
			'type' => 'sometimes|required|in:OpenQuestion',
			'subtype' => 'sometimes|required',
			'answer' => ''
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
