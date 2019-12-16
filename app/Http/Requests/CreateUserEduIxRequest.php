<?php namespace tcCore\Http\Requests;

use tcCore\User;

class CreateUserEduIxRequest extends Request {

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
			'username' => 'required|email|unique:users,username,NULL,'.(new User())->getKeyName().',deleted_at,NULL',
			'name_first' => 'required',
			'name_suffix' => '',
			'name' => '',
			'email' => '',
			'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'password_confirm' => 'required|same:password',
			'session_hash' => '',
			'api_key' => '',
			'external_id' => '',
			'gender' => '',
			'abbreviation' => ''
		];
	}

	public function getValidatorInstance()
	{
		$validator = parent::getValidatorInstance();

		if ($this->has('school_location_id')) {
			$validator->sometimes('external_id', 'unique:users,external_id,NULL,'.(new User())->getKeyName().',school_location_id,' . $this->school_location_id, function ($input) {
				return ((isset($input->school_location_id) && !empty($input->school_location_id)) || (!isset($input->school_location_id) && empty($schoolLocationId)));
			});
		}

		if ($this->has('school_id')) {
			$validator->sometimes('external_id', 'unique:users,external_id,NULL,'.(new User())->getKeyName().',school_id,' . $this->school_id, function ($input) {
				return ((isset($input->school_id) && !empty($input->school_id)) || (!isset($input->school_id) && empty($schoolId)));
			});
		}

		return $validator;
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
