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
	public function loginAndRedirect( TestTake $testTake, Shortcode $shortCode)
	{
	    Auth::login($shortCode->user);
	    Redirect(route('student.test-take', $testTake->uuid));
		//
	}





}
