<?php namespace tcCore\Http\Requests;

use Ramsey\Uuid\Uuid;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class CreateSchoolRequest extends Request {

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
			
			if (isset($data['user_id'])) {
				if (!Uuid::isValid($data['user_id'])) {
					$validator->errors()->add('user_id','Deze gebruiker kon helaas niet terug gevonden worden.');
				}

				$user = User::whereUuid($data['user_id'])->first();

				if (!$user) {
					$validator->errors()->add('user_id','Deze gebruiker kon helaas niet terug gevonden worden.');
				} else {
					$data['user_id'] = $user->getKey();
				}
			}

			if (isset($data['umbrella_organization_id']) && $data['umbrella_organization_id'] !== "0") {
				if (!Uuid::isValid($data['umbrella_organization_id'])) {
					$validator->errors()->add('umbrella_organization_id','Deze koepelorganisatie kon helaas niet terug gevonden worden.');
				}

				$model = UmbrellaOrganization::whereUuid($data['umbrella_organization_id'])->first();

				if (!$model) {
					$validator->errors()->add('umbrella_organization_id','Deze koepelorganisatie kon helaas niet terug gevonden worden.');
				} else {
					$data['umbrella_organization_id'] = $model->getKey();
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
