<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ListSchoolLocationEducationlevelRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{

        return
            Auth::user()->hasRole('Teacher') || Auth::user()->hasRole('Account manager') ||  Auth::user()->hasRole('School manager');
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$this->filterInput();

		return [

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


    protected function failedValidation(Validator $validator) {
	    throw new HttpResponseException(response()->json($this->formatErrors($validator), 422));
	}


    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function getWithValidator($validator){
        $validator->after(function ($validator) {

        });
    }

}
