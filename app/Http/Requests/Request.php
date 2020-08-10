<?php namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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

	public function filterInput()
	{
		try {
			$input = $this->all();
		} catch (\Throwable $th) {
			return;
		}

		//sanitize input to prevent XSS
		//value is passed as reference
		array_walk_recursive($input, function(&$value, $key) {
			if (!empty($value) && is_string($value)) {
				$value = clean($value);
			}			
		});

		return $this->replace($input);
	}
}
