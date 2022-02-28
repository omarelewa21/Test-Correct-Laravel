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
		$this->question = $route->parameter('question');
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
			'type' => 'sometimes|required|in:CompletionQuestion,DrawingQuestion,MatchingQuestion,MultipleChoiceQuestion,OpenQuestion,RankingQuestion,InfoscreenQuestion',
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

	protected function getExtraRulesClass($type){
		return 'tcCore\Http\Requests\Update' . $type . 'Request';
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$this->filterInput();

		if ($this->has('type') && $this->input('type') !== 'Question') {
			$rules = $this->getExtraRulesClass($this->input('type'));
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

	/**
	 * Get the validator instance for the request and
	 * add attach callbacks to be run after validation
	 * is completed.
	 *
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	protected function getValidatorInstance()
	{
		$validator = parent::getValidatorInstance();
		$this->withValidator($validator);
		return $validator;
	}

	public function messages(){
        return [
            'title.required' => 'A title is required',
            'body.required'  => 'A message is required',
        ];
    
    }

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
			(new $extraRulesClass($this->route))->getWithValidator($validator);
			unset($validator->message);
		}
	}

}
