<?php namespace tcCore\Http\Requests;

use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\User;

class UpdateDemoAccountRequest extends Request {

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

		return [];
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
