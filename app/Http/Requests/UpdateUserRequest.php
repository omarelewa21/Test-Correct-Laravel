<?php namespace tcCore\Http\Requests;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Hash;
use tcCore\User;

class UpdateUserRequest extends Request {

	/**
	 * @var User
	 */
	private $user;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->user = $route->parameter('user');
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
			'username' => 'sometimes|required|email|unique:users,username,'.$this->user->getKey().','.$this->user->getKeyName().',deleted_at,NULL',
			'name_first' => '',
			'name_suffix' => '',
			'name' => '',
			'email' => '',
			'old_password' => 'sometimes|required|old_password:'.$this->user->getAttribute('password'),
			'password' => '',
			'session_hash' => '',
			'api_key' => '',
			'external_id' => '',
			'gender' => '',
			'abbreviation' => ''
		];
	}

	public function getValidatorInstance()
	{
		$validator = parent::getValidatorInstance();

		$validator->sometimes('external_id', 'unique:users,external_id,'.$this->user->getKey().','.$this->user->getKeyName().',school_location_id,'.$this->user->getAttribute('school_location_id'), function($input) {
			$schoolLocationId = $this->user->getAttribute('school_location_id');
			return ((isset($input->school_location_id) && !empty($input->school_location_id)) || (!isset($input->school_location_id) && empty($schoolLocationId)));
		});

		$validator->sometimes('external_id', 'unique:users,external_id,'.$this->user->getKey().','.$this->user->getKeyName().',school_id,'.$this->user->getAttribute('school_id'), function($input) {
			$schoolId = $this->user->getAttribute('school_id');
			return ((isset($input->school_id) && !empty($input->school_id)) || (!isset($input->school_id) && empty($schoolId)));
		});

		return $validator;
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

	/**
	 * @param Factory $factory
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	public function validator(Factory $factory) {
		$factory = clone $factory;

		$factory->extend('old_password',
			function ($attribute, $value, $parameters)
			{
				return Hash::check($value, $parameters[0]);
			},
			'Record does not match stored value'
		);

		return $factory->make(
			$this->all(), $this->container->call([$this, 'rules']), $this->messages(), $this->attributes()
		);
	}

}
