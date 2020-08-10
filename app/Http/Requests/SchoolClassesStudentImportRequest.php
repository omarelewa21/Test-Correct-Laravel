<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SchoolClassesStudentImportRequest extends Request {

    protected $schoolLocation;
    protected $schoolClass;
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
        $this->schoolLocation = $this->route('schoolLocation');
        $this->schoolClass = $this->route('schoolClass');

        return
            Auth::user()->hasRole('School manager')
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
        $this->filterInput();

		return [
		    'data' => 'array',
		    'data.*.username' => 'required|email|unique:users,username',
            'data.*.name_first' => 'required',
            'data.*.name' => 'required'
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
        $results = [];
        $flags = ['username' => false, 'name_first' => false, 'name' => false];
        $messages = $validator->errors()->messages();
        foreach ($messages as $key => $value) {
            if (!str_contains($key, 'username') && !$flags['username']) {
                $results[] = 'De emailadressen dienen aanwezig, welgevormd en unique te zijn.';
                $flags['username'] = true;
            }
            else if (str_contains($key, 'name_first') && !$flags['name_first']) {
                $results[] = 'De voornamen zijn verplicht';
                $flags['name_first'];
            }
            else if (str_contains($key, 'name') && !$flags['name']) {
                $results[] = 'De achternamen zijn verplicht';
                $flags['name'] = true;
            }
            else if (str_contains($key,'class')){
                $results[] = 'Het is niet duidelijk om welke klas het gaat';
            }
            if($flags['username'] == true && $flags['name_first'] == true && $flags['name'] == true){
                break;
            }
        }
        return $results;
//        return $validator->errors()->all();
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function getWithValidator($validator){
        $validator->after(function ($validator) {
            if($this->schoolClass == null){
                $validator->errors()->add('class','Er dient een klas opgegeven te worden');
            }
//            $data = request()->input('data');
//            $requiredFields = ['username','name_first','name'];
//            $errorsFound = false;
//            if(is_array($data)){
//                foreach($requiredFields as $field){
//                    $erroFields = [];
//                    if(!array_key_exists($field,$data[0])){
//                        $errorFields[]  = $field;
//                    }
//                    if(count($erroFields)){
//                        $errorsFound = true;
//                        $validator->errors()->add('data',sprintf('Niet alle verplichte velden zijn aanwezig (%s)',implode(',',$erroFields)));
//                    }
//                }
//                if(!$errorsFound) {
//                    foreach ($data as $user) {
//
//                    }
//                }
//            }
//            else{
//                $validator->errors()->add('data','Geen valide data ontvangen');
//            }
        });
    }

}
