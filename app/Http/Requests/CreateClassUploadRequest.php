<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateClassUploadRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{

        $this->schoolLocation = $this->route('schoolLocation');

        return
            Auth::user()->hasRole('Teacher')
            && $this->schoolLocation !== null
            && Auth::user()->school_location_id == $this->schoolLocation->getKey();
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
		    'file' => 'required|file',
            'class' => 'required',
            'education_level_year' => 'required',
            'education_level_id' => 'required',
            'subject' => 'required',
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

    protected function formatErrors(Validator $validator)
    {

        return $validator->errors()->all();

//        $results = [];
//        $flags = ['username' => false, 'name_first' => false, 'name' => false];
//        $messages = $validator->errors()->messages();
//        foreach ($messages as $key => $value) {
//            if (!str_contains($key, 'username') && !$flags['username']) {
//                $results[] = 'De emailadressen dienen aanwezig te zijn en valide';
//                $flags['username'] = true;
//            }
//            else if (str_contains($key, 'name_first') && !$flags['name_first']) {
//                $results[] = 'De voornamen zijn verplicht';
//                $flags['name_first'];
//            }
//            else if (str_contains($key, 'name') && !$flags['name']) {
//                $results[] = 'De achternamen zijn verplicht';
//                $flags['name'] = true;
//            }
//            else if (str_contains($key,'class')){
//                $results[] = 'Het is niet duidelijk om welke klas het gaat';
//            }
//            if($flags['username'] == true && $flags['name_first'] == true && $flags['name'] == true){
//                break;
//            }
//        }
//        return $results;
////        return $validator->errors()->all();
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
