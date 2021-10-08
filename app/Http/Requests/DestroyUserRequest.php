<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use tcCore\TestTakeStatus;

class DestroyUserRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
	    // @TODO no check on school or anything at this moment
		$roles = $this->getUserRoles();
		if (in_array('School manager', $roles) || in_array('Administrator', $roles)) {
			return true;
		} else {
			return false;
		}
	}

	public function rules() {
	    return [];
    }

}
