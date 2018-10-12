<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\CompletionQuestion;

class UpdateCompletionQuestionRequest extends UpdateQuestionRequest {

	/**
	 * @var CompletionQuestion
	 */
	private $completionQuestion;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->completionQuestion = $route->getParameter('completion_question');
		if ($this->completionQuestion instanceof CompletionQuestion) {
			$this->question = $this->completionQuestion->getQuestionInstance();
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
			'type' => 'sometimes|required|in:CompletionQuestion',
			'subtype' => '',
			'rating_method' => ''
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
