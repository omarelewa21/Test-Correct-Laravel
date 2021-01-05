<?php namespace tcCore\Http\Requests;

use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\School;
use tcCore\SchoolClass;

class CreateSchoolClassRequest extends Request
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

        logger([$this->name, request('name', $this->name)]);

        return [
            'subject_id'           => '',
            'education_level_id'   => '',
            'school_year_id'       => '',
            'mentor_id'            => '',
            'manager_id'           => '',
            'name'                 => [
                function ($attribute, $value, $fail) {
                    $schoolClass = SchoolClass::where('school_location_id', $this->school_location_id)
                        ->where('school_year_id', $this->school_year_id)
                        ->where('name', $this->name)->first();
                    if ($schoolClass) {
                        $fail('Deze klasnaam bestaat al in dit schooljaar');
                    }
                }],
            'is_main_school_class' => ''
        ];
    }

    public function prepareForValidation()
    {

        $data = ($this->all());

        if (!Uuid::isValid($data['education_level_id'])) {
            $this->addPrepareForValidationError('education_level_id', 'Dit niveau kon helaas niet teruggevonden worden.');
        } else {
            $educationLevel = EducationLevel::whereUuid($data['education_level_id'])->first();

            if (!$educationLevel) {
                $this->addPrepareForValidationError('education_level_id', 'Dit niveau kon helaas niet teruggevonden worden.');
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

    public function messages()
    {
        return [
            'name.unique' => 'Deze klasnaam bestaat al in dit schooljaar',
        ];
    }
}
