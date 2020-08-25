<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateTestTakeStatusRequest extends Request {

	/**
	 * @var TestTakeStatus
	 */
	private $testTakeStatus;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->testTakeStatus = $route->parameter('test_take_status');
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
			'name' => '',
			'is_individual_status' => ''
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
