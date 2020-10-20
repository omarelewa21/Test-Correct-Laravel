<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Ramsey\Uuid\Uuid;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class UpdateSchoolRequest extends Request {

	/**
	 * @var School
	 */
	private $school;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->school = $route->parameter('school');
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
			'name' => ''
		];
	}

    public function prepareForValidation()
    {

			$data = ($this->all());
			
			if (isset($data['user_id'])) {
				if (!Uuid::isValid($data['user_id'])) {
					$this->addPrepareForValidationError('user_id','Deze gebruiker kon helaas niet terug gevonden worden.');
				}

				$user = User::whereUuid($data['user_id'])->first();

				if (!$user) {
					$this->addPrepareForValidationError('user_id','Deze gebruiker kon helaas niet terug gevonden worden.');
				} else {
					$data['user_id'] = $user->getKey();
				}
			}

			if (isset($data['umbrella_organization_id']) && $data['umbrella_organization_id'] !== "0") {
				if (!Uuid::isValid($data['umbrella_organization_id'])) {
					$this->addPrepareForValidationError('umbrella_organization_id','Deze koepelorganisatie kon helaas niet terug gevonden worden.');
				}

				$model = UmbrellaOrganization::whereUuid($data['umbrella_organization_id'])->first();

				if (!$model) {
					$this->addPrepareForValidationError('umbrella_organization_id','Deze koepelorganisatie kon helaas niet terug gevonden worden.');
				} else {
					$data['umbrella_organization_id'] = $model->getKey();
				}
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
