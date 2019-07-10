<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateUserRoleRequest extends Request {

	/**
	 * @var UserRole
	 */
	private $userRole;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->userRole = $route->parameter('user_role');
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
			'user_id' => '',
			'role_id' => ''
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
