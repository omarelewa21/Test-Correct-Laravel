<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateSectionRequest extends Request {

	/**
	 * @var Section
	 */
	private $section;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->section = $route->parameter('section');
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
