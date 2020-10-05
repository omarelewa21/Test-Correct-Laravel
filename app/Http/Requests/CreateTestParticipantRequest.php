<?php namespace tcCore\Http\Requests;

use Ramsey\Uuid\Uuid;
use tcCore\TestParticipant;
use tcCore\User;

class CreateTestParticipantRequest extends Request {

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
			'test_take_id' => '',
			'user_id' => '',
			'test_take_status_id' => '',
			'note' => ''
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

            if(isset($data["user_id"])){
                $userIds = [];
                $hasUuids = false;
                collect($data['user_id'])->each(function($uuid) use (&$userIds, &$hasUuids){
                   if(Uuid::isValid($uuid)){
                       $user = User::whereUUid($uuid)->first();
                       if($user){
                           $userIds[] = $user->getKey();
                       }
                       $hasUuids = true;
                   }
                });
                if($hasUuids){
                    $data['user_id'] = $userIds;
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
