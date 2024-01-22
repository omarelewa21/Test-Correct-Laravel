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
			'type' => 'required|in:CompletionQuestion,DrawingQuestion,MatchingQuestion,MultipleChoiceQuestion,OpenQuestion,RankingQuestion,GroupQuestion,InfoscreenQuestion,MatrixQuestion,RelationQuestion',
			'question' => 'required',
			'score' => 'required|integer|min:0',
			'maintain_position' => 'required|in:0,1',
			'discuss' => 'required|in:0,1',
			'decimal_score' => 'required|in:0,1',
			'rtti' => 'in:R,T1,T2,I|nullable',
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
		$this->filterInput();

		$baseRules = $this->baseRules();

		if ($this->has('type') && $this->input('type') !== 'Question') {
			$extraRulesClass = $this->getExtraRulesClass($this->input('type'));
			if (class_exists($extraRulesClass) && method_exists($extraRulesClass, 'rules')) {
				return array_merge($baseRules, (new $extraRulesClass())->rules());
			}
		}

		return $baseRules;
	}

	protected function getExtraRulesClass($type){
		return 'tcCore\Http\Requests\Create' . $type . 'Request';
	}

	/**
	 * Get the sanitized input for the request.
	 *
	 * @return array172
	 */
	public function sanitize()
	{
		return $this->all();
	}

	/**
	 * Get the validator instance for the request and
	 * add attach callbacks to be run after validation
	 * is completed.
	 *
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
//	protected function getValidatorInstance()
//	{
//		$validator = parent::getValidatorInstance();
//		//$this->withValidator($validator);
//		return $validator;
//	}

	/**
	 * Configure the validator instance.
	 *
	 * @param  \Illuminate\Validation\Validator $validator
	 * @return void
	 */
	// on version 5.7 this method is called but needs to be public.
	// You need to remove the protected function getValidatorInstance() if that is the case.
	public function withValidator($validator)
	{
		$extraRulesClass = $this->getExtraRulesClass($this->input('type'));
		if (class_exists($extraRulesClass) && method_exists($extraRulesClass, 'getWithValidator')) {
			(new $extraRulesClass())->getWithValidator($validator);
		}
	}

}
