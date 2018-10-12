<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\MatchingQuestion;

class UpdateMatchingQuestionRequest extends UpdateQuestionRequest {

	/**
	 * @var MatchingQuestion
	 */
	private $matchingQuestion;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->matchingQuestion = $route->getParameter('matching_question');
		if ($this->matchingQuestion instanceof MatchingQuestion) {
			$this->question = $this->matchingQuestion->getQuestionInstance();
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
			'type' => 'sometimes|required|in:MatchingQuestion',
			'subtype' => 'sometimes|required'
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
