<?php namespace tcCore\Http\Requests;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Controllers\UsersController;
use tcCore\Rules\EmailDns;
use tcCore\Rules\NistPasswordRules;
use tcCore\Rules\SchoolLocationUserExternalId;
use tcCore\Rules\SchoolLocationUserExternalIdUpdate;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\User;

class UpdateUserRequest extends Request {

	/**
	 * @var User
	 */
	private $user;
    protected $schoolLocation;

    protected function getAttributeNames() {
        return [
            'password' => __('auth.password')
        ];
    }
	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
//	    logger($route->parameter('user'));
//	    logger(request('user'));
        $this->schoolLocation = Auth::user()->school_location_id;
	    $this->user = $route->parameter('user');
        $authUser = Auth::user();
        if($this->user == $authUser){
            return true;
        }

	    $roles = $this->getUserRoles();
        if (in_array('School manager', $roles)) {
            return true;
        } else {
            return false;
        }
//	    dd(auth()->user());
//        $this->user = auth()->user();
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
        $extra_rule = [];
        $data = request()->all();
        if(array_key_exists('username',$data)){
            $extra_rule['username'] = ['required','email','unique:users,username,'.$this->user->getKey().','.$this->user->getKeyName().',deleted_at,NULL',new EmailDns()];
        }
        $userController = new UsersController();
        if($userController->hasTeacherRole($this->user)){
            $extra_rule['external_id'] = new SchoolLocationUserExternalIdUpdate($this->schoolLocation,$this->user);
        }

		if($this->has('is_examcoordinator') && $this->is_examcoordinator == 1){
			$extra_rule['is_examcoordinator_for'] = 'required|in:NONE,SCHOOL,SCHOOL_LOCATION';
		}

        $rules = collect([
            'username' => 'sometimes,required|email|unique:users,username,'.$this->user->getKey().','.$this->user->getKeyName().',deleted_at,NULL',
            'name_first' => '',
            'name_suffix' => '',
            'name' => '',
            'email' => '',
            'old_password' => 'sometimes|required|old_password:'.$this->user->getAttribute('password'),
            'password' => ['sometimes', ...NistPasswordRules::changePassword($this->user->username, $data['old_password'] ?? null)],
            'password_confirmation' => 'sometimes',
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

		$validator->sometimes('external_id',
                                'unique:users,external_id,'.$this->user->getKey().','.$this->user->getKeyName().',school_location_id,'.$this->user->getAttribute('school_location_id'),
                                    function($input) {
                                        $schoolLocationId = $this->user->getAttribute('school_location_id');
                                        return ((isset($input->school_location_id) && !empty($input->school_location_id)) || (!isset($input->school_location_id) && !empty($schoolLocationId)));
                                    }
                                );

		$validator->sometimes('external_id', 'unique:users,external_id,'.$this->user->getKey().','.$this->user->getKeyName().',school_id,'.$this->user->getAttribute('school_id'), function($input) {
			$schoolId = $this->user->getAttribute('school_id');
			return ((isset($input->school_id) && !empty($input->school_id)) || (!isset($input->school_id) && !empty($schoolId)));
		});

        $validator->setAttributeNames($this->getAttributeNames());

		return $validator;
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
	 * @param Factory $factory
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	public function validator(Factory $factory) {
		$factory = clone $factory;

		$factory->extend('old_password',
			function ($attribute, $value, $parameters)
			{
				return Hash::check($value, $parameters[0]);
			},
            __('auth.passwords_dont_match')
		);

		return $factory->make(
			$this->all(), $this->container->call([$this, 'rules']), $this->messages(), $this->attributes()
		);
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
            if($this->user->getOriginal('demo') == true){
                    $validator->errors()->add('demo', 'Een demo gebruiker kan niet aangepast worden.');
			}

			$data = ($this->all());
			
			//UUID to ID mapping
			if (isset($data['school_id'])) {
				if (!Uuid::isValid($data['school_id'])) {
					$validator->errors()->add('school_id','Deze school kon helaas niet terug gevonden worden.');
				} else {
					$school = School::whereUuid($data['school_id'])->first();

					if (!$school) {
						$validator->errors()->add('school_id','Deze school kon helaas niet terug gevonden worden.');
					} else {
						$data['school_id'] = $school->getKey();
					}
				}
			}

			if (isset($data['add_mentor_school_class'])) {
				if (!Uuid::isValid($data['add_mentor_school_class'])) {
					$validator->errors()->add('add_mentor_school_class','Deze mentor klas kon helaas niet terug gevonden worden.');
				} else {
					$schoolclass = SchoolClass::whereUuid($data['add_mentor_school_class'])->first();

					if (!$schoolclass) {
						$validator->errors()->add('add_mentor_school_class','Deze mentor klas kon helaas niet terug gevonden worden.');
					} else {
						$data['add_mentor_school_class'] = $schoolclass->getKey();
					}
				}
			}

			if (isset($data['manager_school_classes'])) {
				foreach ($data['manager_school_classes'] as $key => $value) {
					if (!Uuid::isValid($value)) {
						$validator->errors()->add('add_mentor_school_class','Deze manager kon helaas niet terug gevonden worden.');
					} else {
						$schoolclass = SchoolClass::whereUuid($value)->first();
	
						if (!$schoolclass) {
							$validator->errors()->add('add_mentor_school_class','Deze manager kon helaas niet terug gevonden worden.');
						} else {
							$data['manager_school_classes'][$key] = $schoolclass->getKey();
						}
					}
				}
			}

			if (isset($data['student_parents_of'])) {
				foreach ($data['student_parents_of'] as $key => $value) {
					if (!Uuid::isValid($value)) {
						$validator->errors()->add('add_mentor_school_class','Deze ouder kon helaas niet terug gevonden worden.');
					} else {
						$user = User::whereUuid($value)->first();
	
						if (!$user) {
							$validator->errors()->add('add_mentor_school_class','Deze ouder kon helaas niet terug gevonden worden.');
						} else {
							$data['student_parents_of'][$key] = $user->getKey();
						}
					}
				}
			}

			if(!array_key_exists('is_examcoordinator', $data) || $data['is_examcoordinator'] == 0){
				$data['is_examcoordinator_for'] = NULL;
			}

			$this->merge($data);
        });
    }

}
