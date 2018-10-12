<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateSchoolYearRequest extends Request {

	/**
	 * @var SchoolYear
	 */
	private $schoolYear;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->schoolYear = $route->getParameter('school_year');
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
		return [
			'year' => ''
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
