<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Role;
use tcCore\Rules\EmailDns;
use tcCore\Rules\SchoolLocationUserExternalId;
use tcCore\Rules\SchoolLocationUserName;
use tcCore\Rules\UsernameUniqueSchool;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\User;

class CreateUserRequest extends Request {

    protected $schoolLocationId;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * @throws \Exception
     */
	public function authorize()
    {
        $this->setSchoolLocationForRequest();
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

        if ($this->userToCreateIsATeacherAndHasUsername($data)) {
            $extra_rule['username'] = [
                'required',
                'email:rfc,filter',
                new SchoolLocationUserName($this->schoolLocationId, $data['username']),
                new UsernameUniqueSchool($this->schoolLocationId, 'teacher'),
                new EmailDns,
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return $fail(sprintf('The user email address contains international characters  (%s).', $value));
                    }
                }];
            $extra_rule['external_id'] = new SchoolLocationUserExternalId($this->schoolLocationId, $data['username']);
            if ($this->has('is_examcoordinator') && $this->is_examcoordinator == 1) {
                $extra_rule['is_examcoordinator_for'] = 'required|in:NONE,SCHOOL,SCHOOL_LOCATION';
            }
        }
		$rules = collect([
			'username' => ['required','email','unique:users,username,NULL,'.(new User())->getKeyName().',deleted_at,NULL',new EmailDns],
			'name_first' => '',
			'name_suffix' => '',
			'name' => '',
			'email' => '',
			'password' => 'sometimes|'.User::getPasswordLengthRule(),
			'session_hash' => '',
			'api_key' => '',
			'external_id' => '',
			'gender' => '',
			'abbreviation' => '',
            'is_examcoordinator' => 'boolean'
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

        $validator->setAttributeNames(['password' => __('auth.password')]);

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

        if(isset($data['is_examcoordinator']) && $data['is_examcoordinator'] == 0) {
            $data['is_examcoordinator_for'] = NULL;
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

    /**
     * @param array $data
     * @return bool
     */
    private function userToCreateIsATeacherAndHasUsername(array $data): bool
    {
        return (isset($data['user_roles']) && collect($data['user_roles'])->contains(Role::TEACHER)) && isset($data['username']);
    }

    private function setSchoolLocationForRequest(): void
    {
        if (Auth::user()->isA('Administrator')) {
            $noSchoolLocationNeededForRoleIds = [5,10,11,12];
            if(request()->has('user_roles') && !collect($noSchoolLocationNeededForRoleIds)->contains(request('user_roles')[0]) ) {
                if (!request()->has('school_location_id')) {
                    throw new \Exception('Administrator provided no school_location_id for the creation of a user.');
                }

                $this->schoolLocationId = request()->get('school_location_id');
            }
            return;
        }

        $this->schoolLocationId = Auth::user()->school_location_id;
    }

}
