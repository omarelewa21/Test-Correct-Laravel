<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Ramsey\Uuid\Uuid;
use tcCore\User;

class UpdateUmbrellaOrganizationRequest extends Request {

    /**
     * @var UmbrellaOrganization
     */
    private $umbrellaOrganization;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->umbrellaOrganization = $route->parameter('umbrella_organization');
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

            $this->merge($data);
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
