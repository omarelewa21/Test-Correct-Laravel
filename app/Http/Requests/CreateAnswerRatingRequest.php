<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use tcCore\Answer;
use tcCore\TestTake;
use tcCore\User;

class CreateAnswerRatingRequest extends Request {

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
			'answer_id' => 'required',
			'user_id' => '',
			'test_take_id' => '',
			'rating' => ''
		];
	}

    public function prepareForValidation()
    {
            $data = ($this->all());
            if (isset($data['user_id'])) {
                if (!Uuid::isValid($data['user_id'])) {
                    $this->addPrepareForValidationError('user_id','Deze gebruiker kon helaas niet terug gevonden worden.');
                } else {
                    $data['user_id'] = User::whereUuid($data['user_id'])->first()->getKey();
                }
            }

            if (isset($data['answer_id'])) {
                if (!Uuid::isValid($data['answer_id'])) {
                    $this->addPrepareForValidationError('answer_id','Dit antwoord kon helaas niet terug gevonden worden.');
                } else {
                    $data['answer_id'] = Answer::whereUuid($data['answer_id'])->first()->getKey();
                }
            }

            if (isset($data['test_take_id'])) {
                if (!Uuid::isValid($data['test_take_id'])) {
                    $this->addPrepareForValidationError('test_take_id','Deze toetsafname kon helaas niet terug gevonden worden.');
                } else {
                    $data['test_take_id'] = TestTake::whereUuid($data['test_take_id'])->first()->getKey();
                }
            } else {
                $answer = Answer::findOrFail($data['answer_id']);
                $testTake = $answer->testParticipant->testTake;
                $data['test_take_id'] = $testTake->getKey();
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
