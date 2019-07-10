<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

class UpdateTestRequest extends Request {

	/**
	 * @var Test
	 */
	private $test;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->test = $route->parameter('test');
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
			'subject_id' => '',
			'education_level_id' => '',
			'period_id' => '',
			'name' => 'sometimes|unique:tests,name,' . $this->test->getKey() . ',id,author_id,' . Auth::id().',deleted_at,NULL,is_system_test,0',
			'abbreviation' => '',
			'kind' => '',
			'status' => '',
			'grade' => '',
			'shuffle' => ''
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
