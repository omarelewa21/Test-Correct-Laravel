<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateLicenseRequest extends Request {

	/**
	 * @var License
	 */
	private $license;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->license = $route->getParameter('license');
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
			'start' => 'sometimes|date_format:Y-m-d',
			'end' => 'sometimes|date_format:Y-m-d',
			'amount' => 'sometimes|numeric|min:1'
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
