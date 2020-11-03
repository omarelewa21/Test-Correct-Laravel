<?php namespace tcCore\Http\Requests;

use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;

class CreateSchoolClassRequest extends Request {

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
			'subject_id' => '',
			'education_level_id' => '',
			'school_year_id' => '',
			'mentor_id' => '',
			'manager_id' => '',
			'name' => '',
			'is_main_school_class' => ''
		];
	}

    public function prepareForValidation()
    {

        $data = ($this->all());

        if(!Uuid::isValid($data['education_level_id'])){
            $this->addPrepareForValidationError('education_level_id','Dit niveau kon helaas niet terug gevonden worden.');
        } else {
            $educationLevel = EducationLevel::whereUuid($data['education_level_id'])->first();

            if (!$educationLevel) {
                $this->addPrepareForValidationError('education_level_id', 'Dit niveau kon helaas niet terug gevonden worden.');
            } else {
                $data['education_level_id'] = $educationLevel->getKey();
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
