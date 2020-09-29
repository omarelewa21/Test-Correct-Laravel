<?php namespace tcCore\Http\Requests;

class CreatePeriodRequest extends Request {

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
		    'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'date|after:start_date',
            'school_year_id' => 'required|integer|exists:school_years,id',
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
