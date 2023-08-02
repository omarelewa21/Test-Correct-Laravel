<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use tcCore\Rules\EmailDns;
use tcCore\SchoolLocation;
use tcCore\User;
use tcCore\SchoolClass;
use tcCore\Http\Controllers\SchoolYearsController;

class SchoolClassesStudentImportRequest extends Request
{
    protected $schoolLocation;
    protected $schoolClass;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->schoolLocation = $this->route('schoolLocation');
        $this->schoolClass = $this->route('schoolClass');

        return
            Auth::user()->hasRole('School manager')
            && $this->schoolLocation !== null
            && Auth::user()->school_location_id == $this->schoolLocation->getKey();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $this->filterInput(); // doesn't work here

        $extra_rule = [];
        $school_class_name_rule = 'sometimes';
        if (is_null($this->schoolClass)) {
            $school_class_name_rule = 'required';
        }

        // unique constraint needs to be added on external_id can only exist within a school if it is the same user (that is username is the currect username)
        foreach ($this->data as $key => $value) {

            if (array_key_exists('username', $value)) {
                $extra_rule[sprintf('data.%d.external_id', $key)] = [
                    Rule::unique('users', 'external_id')
                        ->where('school_location_id', $this->schoolLocation->getKey())
                        ->ignore($value['username'], 'username')
                ];
            }
        }

        $rules = collect([
            'data.*.username'          => [
                'required',
                'email:rfc,filter',
                new EmailDns,
                function ($attribute, $value, $fail) {
                    $requestItem = $this->getRequestItem($attribute);
                    $student = User::whereUsername($value)->first();

                    if (!$student) {
                        return true;
                    }
                    if ($this->externalIdDoesNotMatch($student, $requestItem)) {
                        return $fail(sprintf('The %s has already been taken.', 'external id'));
                    }

                    if ($this->alreadyInDatabaseAndInThisClass($student, $requestItem)) {
                        return $fail(sprintf('The %s has already been taken.', $attribute));
                    }
                    if ($this->alreadyInDatabaseButNotInThisSchoolLocation($student)) {
                        return $fail(sprintf('The %s has already been taken.', $attribute));
                    }
                }

            ],
            'data.*.name_first'        => 'required',
            'data.*.name'              => 'required',
            'data.*.name_suffix'       => '',
            'data.*.gender'            => 'sometimes',
            'data.*.school_class_name' => [
                $school_class_name_rule,
                function ($attribute, $value, $fail) {
                    if ($this->classDoesNotExist($value)) {
                        return $fail(sprintf('school_class_name %s not found.', $attribute));
                    }
                }
            ]
        ]);

        if ($extra_rule === []) {
            $mergedRules = $rules->merge([
                'data.*.external_id' => 'required',
            ]);
        } else {
            $mergedRules = $rules->merge($extra_rule);
        }

        return $mergedRules->toArray();
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

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $data = $this->addDuplicateExternalIdErrors($validator);
            $data = $this->addDuplicateUsernameErrors($validator);
//            $this->addDuplicateUsernameInDatabaseErrors($validator);

            if (isset($data['filter']) && isset($data['filter']['school_location_id']) && Uuid::isValid($data['filter']['school_location_id'])) {
                $item = SchoolLocation::whereUuid($data['filter']['school_location_id'])->first();
                if (!$item) {
                    $validator->errors()->add('school_location_id', 'De school locatie kon niet gevonden worden.');
                } else {
                    $data['filter']['school_location_id'] = $item->getKey();
                }
            }
            $this->merge(['data' => $data]);
        });
    }

    private function addDuplicateExternalIdErrors($validator)
    {
        return $this->addDuplicateErrors($validator, 'external_id', 'Deze import bevat dubbele studentennummers voor dezelfde klas.');
    }

    private function addDuplicateUsernameErrors($validator)
    {
        return $this->addDuplicateErrors($validator, 'username', 'Deze import bevat dubbele emailadressen voor dezelfde klas.');
    }

    private function addDuplicateErrors($validator, $field, $message)
    {
        $data = collect(request()->input('data'));
        $groupedByDuplicates = $data->countBy($field)->filter(fn($count) => $count > 1);

        if ($groupedByDuplicates->count() < $data->count()) {
            $this->addErrorMessagesToValidatorWhenNecessary($message, $field, $groupedByDuplicates, $validator);
        }

        return $data->toArray();
    }

    private function alreadyInDatabaseAndInThisClass($student, $requestItem)
    {
        if (array_key_exists('school_class_name', $requestItem)) {
            $school_class_name = $requestItem['school_class_name'];
            $manager = Auth::user();
            $schoolClass = SchoolClass::where('name', trim($school_class_name))->where('school_location_id', $manager->school_location_id)->first();
            if (!is_null($schoolClass)) {
                return $this->alreadyInDatabaseAndInThisClassGeneric($student, $schoolClass->id);
            }
            return $this->failSilent();
        }
        if (is_null($this->schoolClass)) {
            return $this->failSilent();
        }
        return $this->alreadyInDatabaseAndInThisClassGeneric($student, $this->schoolClass->id);
    }

    private function failSilent()
    {
        return false;
    }

    private function alreadyInDatabaseAndInThisClassGeneric($student, $schoolClassId)
    {
        return (collect($student->studentSchoolClasses)->map(function ($item) {
            return $item->id;
        })->contains($schoolClassId));
    }

    private function alreadyInDatabaseButNotInThisSchoolLocation($student)
    {
        return $student->school_location_id !== $this->schoolLocation->id;
    }

    private function classDoesNotExist($school_class_name)
    {
        $manager = Auth::user();
        $currentSchoolYear = (new SchoolYearsController())->activeSchoolYearInternal();
        if (!$currentSchoolYear) {
            return true;
        }
        return SchoolClass::where('name', trim($school_class_name))
            ->where('school_location_id', $manager->school_location_id)
            ->where('school_year_id', $currentSchoolYear->id)
            ->whereNull('deleted_at')
            ->doesntExist();
    }


    private function getRequestItem($attribute)
    {
        $attributeArray = explode('.', $attribute);
        if (!array_key_exists(1, $attributeArray)) {
            return [];
        }
        $requestIndex = $attributeArray[1];
        if (!array_key_exists('data', request()->all())) {
            return [];
        }
        if (!array_key_exists($requestIndex, request()->all()['data'])) {
            return [];
        }
        return request()->all()['data'][$requestIndex];
    }

    /**
     * @param $field
     * @param $value
     * @return array
     */
    function getDuplicateData($field, $value): array
    {
        return collect($this->data)->filter(fn($item) => ($item[$field] ?? '') === $value)->toArray();
    }

    /**
     * @param array $duplicateData
     * @return bool
     */
    function hasDuplicateDataForTheSameSchoolClass(array $duplicateData): bool
    {
        return collect($duplicateData)->pluck('school_class_name')->unique()->count() !== count($duplicateData);
    }

    /**
     * @param $message
     * @param $field
     * @param $groupedByDuplicates
     * @param $validator
     * @return void
     */
    private function addErrorMessagesToValidatorWhenNecessary($message, $field, $groupedByDuplicates, $validator): void
    {
        collect($this->data)->each(function ($item, $key) use ($message, $field, $groupedByDuplicates, $validator) {
            if ($this->itemHasNoDuplicateField($item[$field] ?? null, $groupedByDuplicates)) {
                return true;
            }

            $duplicateData = $this->getDuplicateData($field, $item[$field] ?? null);
            if ($this->hasDuplicateDataForTheSameSchoolClass($duplicateData)) {
                $validator->errors()->add(sprintf('data.%d.%s', $key, $field), $message);
            }
        });
    }

    function itemHasNoDuplicateField($item, $groupedByDuplicates): bool
    {
        return !array_key_exists($item, $groupedByDuplicates->toArray());
    }

    private function externalIdDoesNotMatch(User $student, array $requestItem): bool
    {
        if (!$student->external_id) {
            return false;
        }
        if ($student->school_location_id !== $this->schoolLocation->getKey()) {
            return false;
        }
        return $student->external_id !== ($requestItem['external_id'] ?? null);
    }
}