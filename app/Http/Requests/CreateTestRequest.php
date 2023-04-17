<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Traits\Modal\TestActions;

class CreateTestRequest extends Request {

	use TestActions;
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
			'subject_id' 			=> ['required', 'integer', $this->getAllowedSubjectsRule()],
			'education_level_id' 	=> ['required', 'integer', $this->getAllowedEducationLevelsRule()],
			'period_id' 			=> ['required', 'integer', $this->getAllowedPeriodsRule()],
			'name' 					=> $this->getNameRulesDependingOnAction(),
			'abbreviation' 			=> 'required|max:5',
			'kind' 					=> '',
			'status' 				=> '',
			'grade' 				=> '',
			'shuffle' 				=> 'required|boolean'
		];
	}

	public function prepareForValidation()
    {
        $data = $this->all();
        if(Uuid::isValid($data['education_level_id'])){
            $educationLevel = EducationLevel::whereUuid($data['education_level_id'])->first();
            if(!$educationLevel){
                $this->addPrepareForValidationError('education_level_id','Dit niveau kon helaas niet terug gevonden worden.');
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
            $data = ($this->all());
            if(strtolower($data['name']) === strtolower(DemoHelper::BASEDEMOTESTNAME)){
                if(Auth::user()->schoolLocation->name !== DemoHelper::SCHOOLLOCATIONNAME){
                    $validator->errors()->add('name','Deze naam is helaas niet beschikbaar voor een toets');
                }
            }
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
