<?php namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use tcCore\Lib\User\Roles;

abstract class Request extends FormRequest {

	public function forbiddenResponse()
	{
		// Optionally, send a custom response on authorize failure
		// (default is to just redirect to initial page with errors)
		//
		// Can return a response, a view, a redirect, or whatever else
		return Response::make('Permission denied!', 403);
	}

	// OPTIONAL OVERRIDE
	/**
	 * @param array $errors
	 * @return JsonResponse
     */
	public function response(array $errors)
	{
		// If you want to customize what happens on a failed validation,
		// override this method.
		// See what it does natively here:
		// https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Http/FormRequest.php
		return new JsonResponse($errors, 422);
	}

	protected function getUserRoles() {
		return Roles::getUserRoles();
	}
}
