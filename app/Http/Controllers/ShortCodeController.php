<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Role;
use tcCore\Http\Requests\CreateRoleRequest;
use tcCore\Http\Requests\UpdateRoleRequest;
use tcCore\Shortcode;
use tcCore\TestTake;

class ShortCodeController extends Controller {

	/**
	 * Display a listing of the roles.
	 *
	 * @return Response
	 */
	public function loginAndRedirect(TestTake $testTake, $shortcode)
	{
	    if ($user = ShortCode::isValid($shortcode)) {
            Auth::login($user);
            // check is a participant of the testTake?

//            logger(route('student.test-take', $testTake->uuid) = route('student.test-take', $testTake->uuid));
            return redirect(
                route('student.test-take', $testTake->uuid)
            );
        }
	    dd('not logged in');
		//
	}





}
