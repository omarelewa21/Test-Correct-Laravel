<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\RankingQuestion;

class UpdateRankingQuestionRequest extends UpdateQuestionRequest {

	/**
	 * @var RankingQuestion
	 */
	private $rankingQuestion;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->rankingQuestion = $route->parameter('ranking_question');
		if ($this->rankingQuestion instanceof RankingQuestion) {
			$this->question = $this->rankingQuestion->getQuestionInstance();
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
			'type' => 'sometimes|required|in:RankingQuestion',
			'random_order' => '',
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
