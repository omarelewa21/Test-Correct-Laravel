<?php namespace tcCore\Http\Requests;

use Ramsey\Uuid\Uuid;
use tcCore\Test;
use tcCore\TestTake;

class CreateTestTakeRequest extends Request
{

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
            'test_id'                 => '',
            'test_take_status'        => '',
            'test_take_group_id'      => '',
            'time_start'              => 'date_format:Y-m-d H:i:s',
            'time_end'                => 'sometimes|date_format:Y-m-d H:i:s',
            'location'                => '',
            'weight'                  => '',
            'note'                    => '',
            'allow_inbrowser_testing' => '',
            'exported_to_rtti'        => ''
        ];
    }


    public function prepareForValidation()
    {

        $data = ($this->all());

        if (isset($data["retake_test_take_id"]) && Uuid::isValid($data['retake_test_take_id'])) {
            $educationLevel = TestTake::whereUuid($data['retake_test_take_git id'])->first();
            if (!$educationLevel) {
                $this->addPrepareForValidationError('retake_test_take_id', 'Deze afname toets kon helaas niet terug gevonden worden.');
            } else {
                $data['retake_test_take_id'] = $educationLevel->getKey();
            }
        }

        if (isset($data["test_id"]) && Uuid::isValid($data['test_id'])) {
            $educationLevel = Test::whereUuid($data['test_id'])->first();
            if (!$educationLevel) {
                $this->addPrepareForValidationError('test_id', 'Deze toets kon helaas niet terug gevonden worden.');
            } else {
                $data['test_id'] = $educationLevel->getKey();
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
