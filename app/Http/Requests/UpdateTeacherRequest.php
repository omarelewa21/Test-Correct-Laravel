<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateTeacherRequest extends Request {

	/**
	 * @var Teacher
	 */
	private $teacher;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->teacher = $route->parameter('teacher');
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
			'user_id' => '',
			'class_id' => '',
			'education_level_id' => '',
			'school_year_id' => ''
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
