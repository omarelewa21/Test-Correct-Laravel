<?php namespace tcCore\Http\Requests;

use Ramsey\Uuid\Uuid;
use tcCore\TestParticipant;

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

            if(isset($data["test_participant_ids"])){
                $newTestParticipantIds = [];
                $hasUuids = false;
                collect($data['test_participant_ids'])->each(function($uid) use (&$newTestParticipantIds, &$hasUuids){
                   if(Uuid::isValid($uid)){
                       $testParticipant = TestParticipant::whereUUid($uid)->first();
                       if($testParticipant){
                           $newTestParticipantIds[] = $testParticipant->getKey();
                       }
                       $hasUuids = true;
                   }
                });
                if($hasUuids){
                    $data['test_participant_ids'] = $newTestParticipantIds;
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
