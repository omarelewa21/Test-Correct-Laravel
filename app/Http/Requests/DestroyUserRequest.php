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

		$roles = $this->getUserRoles();
		if (in_array('School manager', $roles)) {
			return true;
		} else {
			return false;
		}
	}

	public function rules() {
	    return [];
    }

}
