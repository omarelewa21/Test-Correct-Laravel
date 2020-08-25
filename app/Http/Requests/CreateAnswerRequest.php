<?php namespace tcCore\Http\Requests;


class CreateAnswerRequest extends Request {

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
		$this->sanitize();

		return [
			'test_participant_id' => '',
			'question_id' => '',
			'json' => ''
		];
	}

	/**
	 * Get the sanitized input for the request.
	 *
	 * @return array
	 */
	public function sanitize()
	{
		$input = $this->all();

        //unpack json answer and sanitize the input
        $answerJson = json_decode($input['json'], true);

        //sanitize input to prevent XSS
        foreach ($answerJson as $key => $value) {
            $answerJson[$key] = clean($value);
        }

        $input['json'] = json_encode($answerJson, JSON_FORCE_OBJECT);

		return $this->replace($input);
	}

}
