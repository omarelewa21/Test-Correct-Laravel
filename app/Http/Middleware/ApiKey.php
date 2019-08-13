<?php namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use tcCore\User;

class ApiKey {

	function __construct(User $user, Guard $auth)
	{
		$this->user = $user;
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$urlSignature = $request->get('signature');
		if($urlSignature === null){
			//return \Response::make("Please provide a signature for the request.", 400);
		}

		$user = User::where('username', '=', $request->get('user'))->first();
		if($user === null){
			return \Response::make("Please provide a user for the request.", 400);
		}

		if($user->apiKey() === false){
			return \Response::make("Please request an API key.", 401);
		}

		$signature = $this->_calculateSignature($request, $user->apiKey());
		if($signature === false || $signature != $urlSignature){
			//return \Response::make("Request not properly signed.", 403);
		}

		Auth::setUser($user);
//		$this->auth->onceUsingId($user->id);
		return $next($request);
	}

	/**
	 * Returns a signature for the given request, or false on failure.
	 *
	 * @param \Illuminate\Http\Request  $request
	 * @param string $apiKey
	 *
	 * @return bool|mixed
	 */
	private function _calculateSignature($request, $apiKey)
	{
		// Build the complete request, except the signature parameter
		$parameters = $request->query;
		$parameters->remove('signature');
		$query = array();
		foreach($parameters as $key => $value){
			$query[$key] = $value;
		}

		$stringToSign = $request->getPathInfo() . '?' . http_build_query($query);
		return hash_hmac('sha256', $stringToSign, $apiKey);
	}
}
