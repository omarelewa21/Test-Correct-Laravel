<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateRoleRequest extends Request {

	/**
	 * @var Role
	 */
	private $role;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->role = $route->getParameter('role');
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
