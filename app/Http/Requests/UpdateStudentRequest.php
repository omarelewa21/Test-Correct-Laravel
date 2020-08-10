<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateStudentRequest extends Request {

	/**
	 * @var Student
	 */
	private $student;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->student = $route->parameter('student');
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
			//
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
