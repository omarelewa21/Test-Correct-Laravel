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

        $extra_rule = [];
        // unique constraint needs to be added on external_id can only exist within a school if it is the same user (that is username is the currect username)
        foreach ($this->data as $key => $value) {
            $extra_rule[sprintf('data.%d.external_id', $key)] = sprintf('unique:users,external_id,%s,username,school_location_id,%d',$value['username'], $this->schoolLocation->getKey());
        }

		return collect([
		    //'data' => 'array',
		    'data.*.username' => 'required|email',
            'data.*.name_first' => 'required',
            'data.*.name' => 'required',
//dd            'data.0.external_id' => sprintf('unique:users,external_id,erik@sobit.nl,username,school_location_id,%d',$this->schoolLocation->getKey()),
            'data.*.name_suffix' => '',
            'data.*.gender' => '',
        ])->merge($extra_rule)->toArray();
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
