<?php

namespace tcCore\Http\Requests;

use tcCore\CompletionQuestion;
use tcCore\Http\Helpers\QuestionHelper;

/**
 * Should not be called a request as it is only a helper
 */
class CreateCompletionQuestionRequest extends CreateQuestionRequest {

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
			'type' => 'required|in:CompletionQuestion',
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

	/**
	 * Configure the validator instance.
	 *
	 * @param  \Illuminate\Validation\Validator $validator
	 * @return void
	 */
	public function getWithValidator($validator){
		$validator->after(function ($validator) {
			$questionString = request()->input('question');
            $subType = request()->input('subtype');
            CompletionQuestion::validateWithValidator($validator,$questionString,$subType);
		});
	}

}
