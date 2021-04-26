<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;

use tcCore\Rules\EmailDns;
use tcCore\Rules\SchoolLocationUserExternalId;
use tcCore\Rules\SchoolLocationUserName;
use tcCore\Rules\UsernameUniqueSchool;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\User;

class CreateUserRequest extends Request {

    protected $schoolLocation;
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
        $this->schoolLocation = Auth::user()->school_location_id;
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
        $extra_rule = [];
        $data = request()->all();

        if(isset($data['user_roles']) && collect($data['user_roles'])->contains(function($val){return $val == 1;}) && isset($data['username'])){
            $extra_rule['username'] = [  'required',
                'email:rfc,filter',
                new SchoolLocationUserName($this->schoolLocation,$data['username']),
                new UsernameUniqueSchool($this->schoolLocation,'teacher'),
                new EmailDns,
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return $fail(sprintf('The user email address contains international characters  (%s).', $value));
                    }
                }];
            $extra_rule['external_id'] = new SchoolLocationUserExternalId($this->schoolLocation,$data['username']);
        }
		$rules = collect([
			'username' => ['required|email|unique:users,username,NULL,'.(new User())->getKeyName().',deleted_at,NULL',new EmailDns],
			'name_first' => '',
			'name_suffix' => '',
			'name' => '',
			'email' => '',
			'password' => '',
			'session_hash' => '',
			'api_key' => '',
			'external_id' => '',
			'gender' => '',
			'abbreviation' => ''
		]);


        $mergedRules = $rules;
        if ($extra_rule != []) {
            $mergedRules = $rules->merge($extra_rule);
        }
        return $mergedRules->toArray();
	}

	public function getValidatorInstance()
	{
		$validator = parent::getValidatorInstance();

		if ($this->has('school_location_id')) {
			$validator->sometimes('external_id', 'unique:users,external_id,NULL,'.(new User())->getKeyName().',school_location_id,' . $this->school_location_id, function ($input) {
				return ((isset($input->school_location_id) && !empty($input->school_location_id)) || (!isset($input->school_location_id) && empty($schoolLocationId)));
			});
		}

		if ($this->has('school_id')) {
			$validator->sometimes('external_id', 'unique:users,external_id,NULL,'.(new User())->getKeyName().',school_id,' . $this->school_id, function ($input) {
				return ((isset($input->school_id) && !empty($input->school_id)) || (!isset($input->school_id) && empty($schoolId)));
			});
		}

		return $validator;
	}


    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        if($this->request->has('invited_by')){
            $originalUser = User::find(request('invited_by'));
            if (strtolower(explode('@', $originalUser->username)[1]) != strtolower(explode('@', request('username'))[1])) {
                ActingAsHelper::getInstance()->setUser(SchoolHelper::getBaseDemoSchoolUser());
            }
            else{
                ActingAsHelper::getInstance()->setUser($originalUser);
            }
        }

        $validator->after(function ($validator) {
            $data = ($this->all());
            if(isset($data['user_roles']) && collect($data['user_roles'])->contains(function($val){return $val == 1;})){
                $r = null;
                try {
                    $r = SchoolYearRepository::getCurrentSchoolYear();
                }
                catch(\Exception $e){
                    $r = null;
                }
                if(null === $r){
                    $validator->errors()->add('user_roles','U kunt een docent pas aanmaken nadat u een actuele periode heeft aangemaakt. Dit doet u door als schoolbeheerder in het menu Database -> Schooljaren een schooljaar aan te maken met een periode die in de huidige periode valt.');
                }
			}

			$this->addPrepareForValidationErrorsToValidatorIfNeeded($validator);

        });
    }

    public function prepareForValidation()
    {
        $data = $this->all();
        //UUID to ID mapping
        if (isset($data['school_id'])) {
            if (!Uuid::isValid($data['school_id'])) {
                $this->addPrepareForValidationError('school_id','Deze school kon helaas niet terug gevonden worden.');
            } else {
                $school = School::whereUuid($data['school_id'])->first();

                if (!$school) {
                    $this->addPrepareForValidationError('school_id','Deze school kon helaas niet terug gevonden worden.');
                } else {
                    $data['school_id'] = $school->getKey();
                }
            }
        }

        if (isset($data['add_mentor_school_class'])) {
            if (!Uuid::isValid($data['add_mentor_school_class'])) {
                $this->addPrepareForValidationError('add_mentor_school_class','Deze mentor klas kon helaas niet terug gevonden worden.');
            } else {
                $schoolclass = SchoolClass::whereUuid($data['add_mentor_school_class'])->first();

                if (!$schoolclass) {
                    $this->addPrepareForValidationError('add_mentor_school_class','Deze mentor klas kon helaas niet terug gevonden worden.');
                } else {
                    $data['add_mentor_school_class'] = $schoolclass->getKey();
                }
            }
        }

        if (isset($data['manager_school_classes'])) {
            foreach ($data['manager_school_classes'] as $key => $value) {
                if (!Uuid::isValid($value)) {
                    $this->addPrepareForValidationError('add_mentor_school_class','Deze manager kon helaas niet terug gevonden worden.');
                } else {
                    $schoolclass = SchoolClass::whereUuid($value)->first();

                    if (!$schoolclass) {
                        $this->addPrepareForValidationError('add_mentor_school_class','Deze manager kon helaas niet terug gevonden worden.');
                    } else {
                        $data['manager_school_classes'][$key] = $schoolclass->getKey();
                    }
                }
            }
        }

        if (isset($data['student_parents_of'])) {
            foreach ($data['student_parents_of'] as $key => $value) {
                if (!Uuid::isValid($value)) {
                    $this->addPrepareForValidationError('add_mentor_school_class','Deze ouder kon helaas niet terug gevonden worden.');
                } else {
                    $schoolclass = User::whereUuid($value)->first();

                    if (!$schoolclass) {
                        $this->addPrepareForValidationError('add_mentor_school_class','Deze ouder kon helaas niet terug gevonden worden.');
                    } else {
                        $data['student_parents_of'][$key] = $schoolclass->getKey();
                    }
                }
            }
        }

        $this->merge($data);
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
