<?php namespace tcCore\Http\Requests;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use tcCore\Rules\NistPasswordRules;
use tcCore\User;

class UpdatePasswordForUserRequest extends Request {

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
        $authUser = Auth::user();

        if (!$authUser->hasRole('Teacher',$authUser)) {
            return false;
        } else {
            $class_id = request('class_id');
            $hasClass = false;
            foreach($authUser->teacher as $t){
                if($t->class_id == $class_id){
                    $hasClass = true;
                }
            }
            if(!$hasClass){
                return false;
            }
            $hasClass = false;
            foreach($this->user->students as $s){
                if($s->class_id == $class_id){
                    $hasClass = true;
                }
            }
            if(!$hasClass){
                return false;
            }
        }
        return true;
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
			'password' => NistPasswordRules::changePassword($this?->user->username)
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

    public function attributes()
    {
        return [
            'password' => __('auth.password')
        ];
    }

}
