<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Request as RequestObj;
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
//                    'required',
                    Rule::unique('users', 'external_id')
                        ->where('school_location_id', $this->schoolLocation->getKey())
                        ->ignore($value['username'], 'username')
                ];
            }
        }

        $rules = collect([
            'data.*.username'          => ['required', 'email:rfc,filter', new EmailDns, function ($attribute, $value, $fail) {

                if (strpos($value, '&') > NULL) {
                    return $fail(sprintf('The email address contains an ampersand symbol  (%s).', $value));
                }

                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {

                    return $fail(sprintf('The email address contains invalid or international characters  (%s).', $value));
                }
                $requestItem = $this->getRequestItem($attribute);
                $student = User::whereUsername($value)->first();

                if ($student) {
                    if ($this->alreadyInDatabaseAndInThisClass($student, $requestItem)) {
                        return $fail(sprintf('The %s has already been taken.', $attribute));
                    }
                    if ($this->alreadyInDatabaseButNotInThisSchoolLocation($student)) {
                        return $fail(sprintf('The %s has already been taken.', $attribute));
                    }
                }
            }],
            'data.*.name_first'        => 'required',
            'data.*.name'              => 'required',
            'data.*.name_suffix'       => '',
            'data.*.gender'            => 'sometimes',
            'data.*.school_class_name' => [$school_class_name_rule, function ($attribute, $value, $fail) {
                if ($this->classDoesNotExist($value)) {
                    return $fail(sprintf('school_class_name %s not found.', $attribute));
                }
            }]
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

//    public function messages()
//    {
//        return [
//            'data.*.username.email' => 'lorem',
//            'data.*.username.required' => 'sit',
//        ];
//    }

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

        if ($groupedByDuplicates->count() >= $data->count()) {
            return $data->toArray();
        }

        collect($this->data)->each(function ($item, $key) use ($message, $field, $groupedByDuplicates, $validator) {
            if (array_key_exists($item[$field] ?? null, $groupedByDuplicates->toArray())) {

                $duplicateData = $this->getDuplicateData($field, $item[$field]);
                if ($this->hasDuplicateDataForTheSameSchoolClass($duplicateData)) {
                    $validator->errors()->add(
                        sprintf('data.%d.%s', $key, $field),
                        $message
                    );
                }

            }
        });
    }

    private function alreadyInDatabaseAndInThisClass($student, $requestItem)
    {
        if (array_key_exists('school_class_name', $requestItem)) {
            $school_class_name = $requestItem['school_class_name'];
            $manager = Auth::user();
            $schoolClass = SchoolClass::where('name', trim($school_class_name))->where('school_location_id', $manager->school_location_id)->first();
            if (!is_null($schoolClass)) {
                return $this->alreadyInDatabaseAndInThisClassGeneric($student, $schoolClass->id);
            } else {
                return $this->failSilent();
            }
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
        $schoolClass = SchoolClass::where('name', trim($school_class_name))
            ->where('school_location_id', $manager->school_location_id)
            ->where('school_year_id', $currentSchoolYear->id)
            ->whereNull('deleted_at')
            ->first();
        if (is_null($schoolClass)) {
            return true;
        }
        return false;
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
     * @param $item
     * @return array
     */
    function getDuplicateData($field, $item): array
    {
        $duplicateExternalIdDataKeys = array_keys(
            array_column($this->data, $field),
            $item
        );

        $duplicateData = [];
        foreach ($duplicateExternalIdDataKeys as $duplicateKey) {
            $duplicateData[] = $this->data[$duplicateKey];
        }
        return $duplicateData;
    }

    /**
     * @param array $duplicateData
     * @return bool
     */
    function hasDuplicateDataForTheSameSchoolClass(array $duplicateData): bool
    {
        return collect($duplicateData)->pluck('school_class_name')->unique()->count() !== count($duplicateData);
    }
}