<?php namespace tcCore\Http\Requests;
use tcCore\MatchingQuestion;
use Illuminate\Support\Str;

class CreateMatchingQuestionRequest extends CreateQuestionRequest {

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
			'type' => 'required|in:MatchingQuestion',
			'subtype' => 'required'
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


	/**
	 * Configure the validator instance.
	 *
	 * @param  \Illuminate\Validation\Validator $validator
	 * @return void
	 */
	public function getWithValidator($validator)
	{
		$validator->after(function ($validator) {
			if(Str::lower(request()->input('subtype')) === 'classify'){
				$answers = request()->input('answers');
				MatchingQuestion::validateWithValidator($validator, $answers);
			}
        });
	}

}
