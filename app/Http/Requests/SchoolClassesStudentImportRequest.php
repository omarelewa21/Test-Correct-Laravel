<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Ramsey\Uuid\Uuid;
use tcCore\SchoolLocation;

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
		    //'data' => 'array',
		    'data.*.username' => 'required|email',
            'data.*.name_first' => 'required',
            'data.*.name' => 'required',
            'data.*.external_id' => sprintf('unique:users,external_id,NULL,id,school_location_id,%d',$this->schoolLocation->getKey()),
            'data.*.name_suffix' => '',
            'data.*.gender' => '',
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


//    protected function failedValidation(Validator $validator) {
//	    throw new HttpResponseException(response()->json($this->formatErrors($validator), 422));
//	}

//    protected function formatErrors(Validator $validator)
//    {
//        $results = [];
//        $flags = ['username' => false, 'name_first' => false, 'name' => false];
//        $messages = $validator->errors()->messages();
//        logger($messages);
//        foreach ($messages as $key => $value) {
//            if (str_contains($key, 'username') && !$flags['username']) {
//                $results[] = 'De emailadressen dienen aanwezig, welgevormd en unique te zijn.';
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
//    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator){
        $validator->after(function ($validator) {
            if($this->schoolClass == null){
                $validator->errors()->add('class','Er dient een klas opgegeven te worden');
            }

            $data = collect(request()->input('data'));
            $uniqueFields = ['external_id'];
            $groupedByDuplicates = $data->groupBy(function($row, $key) {
                return $row['external_id'];
            })->map(function($item) {
                return collect($item)->count();
            })->filter(function($item, $key){
                return $item > 1;
            });


            if ($groupedByDuplicates->count() < $data->count()) {
                collect($this->data)->each(function($item, $key)  use ($groupedByDuplicates, $validator){
                    if (array_key_exists( $item['external_id'], $groupedByDuplicates->toArray())) {
                        $validator->errors()->add(
                            sprintf('data.%d.external_id', $key),
                            'Deze import bevat dubbele studentennummers'
                        );
                    }
                });
            }

            $data = $data->toArray();

            if(isset($data['filter']) && isset($data['filter']['school_location_id']) && Uuid::isValid($data['filter']['school_location_id'])){
                $item = SchoolLocation::whereUuid($data['filter']['school_location_id'])->first();
                if(!$item){
                        $validator->errors()->add('school_location_id','De school locatie kon niet gevonden worden.');
                    } else {
                        $data['filter']['school_location_id'] = $item->getKey();
                    }
            }
            $this->merge(['data' => $data]);
        });
    }

}
