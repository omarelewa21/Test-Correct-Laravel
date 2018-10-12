<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateAnswerRatingRequest extends Request {

	/**
	 * @var AnswerRating
	 */
	private $answerRating;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->answerRating = $route->getParameter('answer_rating');
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
			'answer_id' => '',
			'user_id' => '',
			'test_take_id' => '',
			'rating' => ''
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
