<?php namespace tcCore\Http\Requests;

use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\User;

class CreateUserRequest extends Request {

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
		return [
			'username' => 'required|email|unique:users,username,NULL,'.(new User())->getKeyName().',deleted_at,NULL',
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
		];
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
