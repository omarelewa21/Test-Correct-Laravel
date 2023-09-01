<?php namespace tcCore\Http\Requests;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use tcCore\Lib\User\Roles;

abstract class Request extends FormRequest {

    protected $prepareForValidationErrors = [];

    protected function addPrepareForValidationError($key, $error)
    {
        $this->prepareForValidationErrors[$key] = $error;
    }

    protected function hasPrepareForValidationErrors()
    {
        return (bool) count($this->prepareForValidationErrors);
    }

    protected function getPrepareForValidationErrors()
    {
        return $this->prepareForValidationErrors;
    }

    protected function addPrepareForValidationErrorsToValidatorIfNeeded(\Illuminate\Validation\Validator $validator)
    {
        if($this->hasPrepareForValidationErrors()){
            foreach($this->getPrepareForValidationErrors() as $key => $error){
                $validator->errors()->add($key,$error);
            }
        }
    }

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

        static::filter($input);

        return $this->replace($input);
    }

    public static function filter(&$input)
    {
        //sanitize input to prevent XSS
        //value is passed as reference
        if (is_array($input)) {
            array_walk_recursive($input, function (&$value, $key) {
                if (!empty($value) && is_string($value)) {
                    $value = clean($value);
                }
            });
        } else if($input instanceof Collection) {
            $input->transform(function ($a) {
                return self::filter($a);
            });
        } elseif (is_bool($input) || is_int($input) || is_float($input) || $input instanceof UploadedFile || is_null($input)){
            // we don't do anything.
            // And as a failsafe we fall back to the string
        } else {
            $input = clean($input);
        }
    }
}