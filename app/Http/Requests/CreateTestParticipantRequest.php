<?php namespace tcCore\Http\Requests;

use Ramsey\Uuid\Uuid;
use tcCore\SchoolClass;
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

            if(isset($data["test_participant_ids"])){
                $testParticipantIds = [];
                $hasUuids = false;
                collect($data['test_participant_ids'])->each(function($uuid) use (&$testParticipantIds, &$hasUuids){
                    if(Uuid::isValid($uuid)){
                        $_tp = TestParticipant::whereUUid($uuid)->first();
                        if($_tp){
                            $testParticipantIds[] = $_tp->getKey();
                        }
                        $hasUuids = true;
                    }
                });
                if($hasUuids){
                    $data['test_participant_ids'] = $testParticipantIds;
                }
            }

            if(isset($data["school_class_ids"])){
                $ids = [];
                $hasUuids = false;
                collect($data['school_class_ids'])->each(function($uuid) use (&$ids, &$hasUuids){
                    if(Uuid::isValid($uuid)){
                        $_model = SchoolClass::whereUUid($uuid)->first();
                        if($_model){
                            $ids[] = $_model->getKey();
                        }
                        $hasUuids = true;
                    }
                });
                if($hasUuids){
                    $data['school_class_ids'] = $ids;
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
