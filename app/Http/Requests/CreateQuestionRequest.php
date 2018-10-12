<?php namespace tcCore\Http\Requests;

use PhpSpec\Exception\Exception;

class CreateQuestionRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	protected function baseRules() {
		return [
			'type' => 'required|in:CompletionQuestion,DrawingQuestion,MatchingQuestion,MultipleChoiceQuestion,OpenQuestion,RankingQuestion,GroupQuestion',
			'question' => 'required',
			'score' => 'required|integer|min:0',
			'maintain_position' => 'required|in:0,1',
			'discuss' => 'required|in:0,1',
			'decimal_score' => 'required|in:0,1',
			'rtti' => 'in:R,T1,T2,I',
			'add_to_database' => 'required|in:0,1'
		];
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$baseRules = $this->baseRules();

		if ($this->has('type') && $this->input('type') !== 'Question') {
			$extraRules = 'tcCore\Http\Requests\Create' . $this->input('type') . 'Request';
			if (class_exists($extraRules) && method_exists($extraRules, 'rules')) {
				return array_merge($baseRules, (new $extraRules())->rules());
			}
		}

		return $baseRules;
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
