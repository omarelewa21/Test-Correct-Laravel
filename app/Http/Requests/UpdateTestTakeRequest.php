<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

class UpdateTestTakeRequest extends Request {

	/**
	 * @var TestTake
	 */
	private $testTake;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->testTake = $route->parameter('test_take');
	}

	/**
	 * Determine if the user is authorized to make this request.
     * only invigilators may change the test take data
	 *
	 * @return bool
	 */
	public function authorize()
	{
        return $this->testTake->isAllowedToView(Auth::user());
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
			'test_id' => '',
			'test_take_status' => '',
			'test_take_group_id' => '',
			'time_start' => 'date_format:Y-m-d H:i:s',
			'time_end' => 'sometimes|date_format:Y-m-d H:i:s',
			'location' => '',
			'weight' => '',
			'note' => '',
            'allow_inbrowser_testing' => '',
            'guest_accounts' => ''
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
