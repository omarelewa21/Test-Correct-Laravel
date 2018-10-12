<?php namespace tcCore\Http\Requests;

class CreateSchoolClassRequest extends Request {

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
			'subject_id' => '',
			'education_level_id' => '',
			'school_year_id' => '',
			'mentor_id' => '',
			'manager_id' => '',
			'name' => '',
			'is_main_school_class' => ''
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
