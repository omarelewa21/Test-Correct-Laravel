<?php namespace tcCore\Http\Controllers\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use tcCore\Http\Controllers\Controller;
use tcCore\User;

class AuthController extends Controller {

	function __construct(User $user, Guard $auth)
	{
		$this->user = $user;
		$this->auth = $auth;
	}

	public function getApiKey(Request $request)
	{
		$user = $request->get('user');
		$password = $request->get('password');

		if($this->auth->once(['username' => $user, 'password' => $password])){
			$user = $this->auth->user();
			$user->setAttribute('session_hash', $user->generateSessionHash());
			$user->save();
			$user->load('roles');

			$hidden = $user->getHidden();

			if(($key = array_search('api_key', $hidden)) !== false) {
				unset($hidden[$key]);
			}
			if(($key = array_search('session_hash', $hidden)) !== false) {
				unset($hidden[$key]);
			}

			$user->setHidden($hidden);

			return new JsonResponse($user);
		} else {
			return \Response::make("Invalid credentials.", 403);
		}
	}
}
