<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;
use tcCore\EanCode;
use tcCore\Http\Helpers\EduIxService;
use tcCore\SchoolLocation;
use tcCore\User;

class CreateUserEduIxRequest extends Request
{

    private $service;

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
            'username'         => 'required|email|unique:users,username,NULL,' . (new User())->getKeyName() . ',deleted_at,NULL',
            'name_first'       => 'required',
            'name_suffix'      => '',
            'name'             => '',
            'email'            => '',
            'password'         => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'password_confirm' => 'required|same:password',
            'session_hash'     => '',
            'api_key'          => '',
            'external_id'      => '',
            'gender'           => '',
            'abbreviation'     => '',
            'session_id'       => '',
            'edu_ix_signature' => '',
        ];
    }

//	public function getValidatorInstance()
//	{
//		$validator = parent::getValidatorInstance();
//
//		if ($this->has('school_location_id')) {
//			$validator->sometimes('external_id', 'unique:users,external_id,NULL,'.(new User())->getKeyName().',school_location_id,' . $this->school_location_id, function ($input) {
//				return ((isset($input->school_location_id) && !empty($input->school_location_id)) || (!isset($input->school_location_id) && empty($schoolLocationId)));
//			});
//		}
//
//		if ($this->has('school_id')) {
//			$validator->sometimes('external_id', 'unique:users,external_id,NULL,'.(new User())->getKeyName().',school_id,' . $this->school_id, function ($input) {
//				return ((isset($input->school_id) && !empty($input->school_id)) || (!isset($input->school_id) && empty($schoolId)));
//			});
//		}
//
//		return $validator;
//	}

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
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $this->initEduIxServiceOrAddError($validator);

        $validator->after(function ($validator) {
            $this->checkDigiDeliveryId($validator);
            $this->checkEan($validator);
            $this->checkSchoolLocation($validator);
        });
    }

    private function checkSchoolLocation(Validator $validator)
    {
        if (!SchoolLocation::where('edu_ix_organisation_id', $this->service->getHomeOrganizationId())->first()) {
            $validator->errors()->add('school', 'Deze school is kennen wij niet neem contact op met de helpdesk');
        }
    }

    private function checkEan(Validator $validator)
    {
        if (!EanCode::where('ean', $this->service->getEan())->first()) {
            $validator->errors()->add('ean', 'De geassocieerde productcode kennen wij niet, neem contact op met de helpdesk');
        }
    }

    private function checkDigiDeliveryId($validator)
    {
//        if (!EduIxRegistation::valid($this->service->getDigiDeliveryId())) {
//            $validator->erros()->add('digi_delivery_id', 'Deze licentie is reeds in gebruik');
//        }
    }

    /**
     * @param $validator
     */
    private function initEduIxServiceOrAddError($validator): void
    {

        try {
            $this->service = new EduIxService(
                request('session_id'),
                request('edu_ix_signature')
            );
        } catch (Exception $e) {
            $validator->errors()->add('edu_ix_organisation_id', $e->getMessage());
        }
    }

}
