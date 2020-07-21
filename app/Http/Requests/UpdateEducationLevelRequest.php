<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateEducationLevelRequest extends Request {

	/**
	 * @var EducationLevel
	 */
	private $educationLevel;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->educationLevel = $route->parameter('education_level');
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
	public function rules()
	{
		$this->filterInput();

		return [
			'name' => ''
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
