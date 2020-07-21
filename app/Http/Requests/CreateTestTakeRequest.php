<?php namespace tcCore\Http\Requests;

class CreateTestTakeRequest extends Request {

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
		$this->filterInput();

		return [
			'test_id' => '',
			'test_take_status' => '',
			'test_take_group_id' => '',
			'time_start' => 'date_format:Y-m-d H:i:s',
			'time_end' => 'sometimes|date_format:Y-m-d H:i:s',
			'location' => '',
			'weight' => '',
			'note' => ''
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
