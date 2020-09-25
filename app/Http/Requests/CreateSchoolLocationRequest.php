<?php namespace tcCore\Http\Requests;

use Ramsey\Uuid\Uuid;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

class CreateSchoolLocationRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
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
			'name' => ''
		];
	}

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = ($this->all());
            if(isset($data['customer_code']) && SchoolLocation::where('customer_code',$data['customer_code'])->count() > 0){
                $validator->errors()->add('customer_code','Er bestaat al een school locatie met dit klantnummer');
			}
			
			if(!Uuid::isValid($data['user_id'])){
				$validator->errors()->add('user_id','Deze gebruiker kon helaas niet worden gevonden.');
			} else if (!Uuid::isValid($data['school_id'])) {
				$validator->errors()->add('school_id','Deze school kon helaas niet worden gevonden.');
			}

			if (isset($data['user_id'])) {
				$user = User::whereUuid($data['user_id'])->first();

				if (!$user) {
					$validator->errors()->add('user_id','Deze gebruiker kon helaas niet worden gevonden.');
				} else {
					$data['user_id'] = $user->getKey();
				}
			}
	
			if (isset($data['school_id'])) {
				$school = School::whereUuid($data['school_id'])->first();

				if (!$school) {
					$validator->errors()->add('school_id','Deze school kon helaas niet worden gevonden.');
				} else {
					$data['school_id'] = $school->getKey();
				}
			}

			request()->merge($data);
        });
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

}
