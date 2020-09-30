<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdatePeriodRequest extends Request {

	/**
	 * @var Period
	 */
	private $period;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->period = $route->parameter('period');
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
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'date|after:start_date',
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
