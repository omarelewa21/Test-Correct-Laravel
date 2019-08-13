<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateRankingQuestionAnswerRequest extends Request {

	/**
	 * @var RankingQuestionAnswer
	 */
	private $rankingQuestionAnswer;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->rankingQuestionAnswer = $route->parameter('ranking_question_answer');
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
			'answer' => '',
			'order' => '',
			'correct_order' => ''
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
