<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateQuestionRequest extends Request {

	/**
	 * @var Question
	 */
	protected $question;

	/**
	 * @var Route
	 */
	protected $route;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->route = $route;
		$this->question = $route->getParameter('question');
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
	protected function baseRules()
	{
		return [
			'type' => 'sometimes|required|in:CompletionQuestion,DrawingQuestion,MatchingQuestion,MultipleChoiceQuestion,OpenQuestion,RankingQuestion',
			'question' => 'sometimes|required',
			'order' => 'sometimes|required|integer|min:0',
			'score' => 'sometimes|required|integer|min:0',
			'maintain_position' => 'sometimes|required|in:0,1',
			'discuss' => 'sometimes|required|in:0,1',
			'decimal_score' => 'sometimes|required|in:0,1',
			//'rtti' => 'sometimes|in:,R,T1,T2,I',
			'add_to_database' => 'sometimes|required|in:0,1'
		];
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		if ($this->has('type') && $this->input('type') !== 'Question') {
			$rules = 'tcCore\Http\Requests\Update' . $this->input('type') . 'Request';
			if (class_exists($rules) && method_exists($rules, 'rules')) {
				return (new $rules($this->route))->rules();
			}
		} else {
			$rules = 'tcCore\Http\Requests\Update' . (new \ReflectionClass($this->question))->getShortName() . 'Request';
			if (class_exists($rules) && method_exists($rules, 'rules')) {
				return (new $rules($this->route))->rules();
			}
		}

		return $this->baseRules();
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
