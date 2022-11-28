<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Ramsey\Uuid\Uuid;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

class UpdateSchoolLocationRequest extends Request {

	/**
	 * @var SchoolLocation
	 */
	private $schoolLocation;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->schoolLocation = $route->parameter('school_location');
	}

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
			'name' => 'string|max:100'
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

    public function prepareForValidation()
    {

            $data = ($this->all());
            if(isset($data['customer_code'])){
                $schoolLocation = SchoolLocation::where('customer_code',$data['customer_code'])->first();
                if($schoolLocation && $schoolLocation->getKey() != $this->schoolLocation->getKey()) {
                    $this->addPrepareForValidationError('customer_code', 'Er bestaat al een school locatie met dit klantnummer');
                }
			}
			
			if (isset($data['user_id'])) {
                if(!Uuid::isValid($data['user_id'])){
                    $this->addPrepareForValidationError('user_id','Deze gebruiker kon helaas niet worden gevonden.');
                }

                $user = User::whereUuid($data['user_id'])->first();

				if (!$user) {
					$this->addPrepareForValidationError('user_id','Deze gebruiker kon helaas niet worden gevonden.');
				} else {
					$data['user_id'] = $user->getKey();
				}
			}
	
			if (isset($data['school_id']) && $data['school_id']) {
                if (!Uuid::isValid($data['school_id'])) {
                    $this->addPrepareForValidationError('school_id', 'Deze school kon helaas niet worden gevonden.');
                } else {
                    $school = School::whereUuid($data['school_id'])->first();

                    if (!$school) {
                        $this->addPrepareForValidationError('school_id', 'Deze school kon helaas niet worden gevonden.');
                    } else {
                        $data['school_id'] = $school->getKey();
                    }
                }
			}

			if (isset($data['lvs_type']) && $data['lvs_type'] == '') {
			    $data['lvs_type'] = null;
            }
            if (isset($data['sso_type']) && $data['sso_type'] == '') {
                $data['sso_type'] = null;
            }

			$this->merge($data);

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
            $this->addPrepareForValidationErrorsToValidatorIfNeeded($validator);
        });
    }

}
