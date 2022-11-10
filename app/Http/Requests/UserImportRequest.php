<?php

namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Rules\EmailDns;
use tcCore\Rules\SameSchoollocationSameExternalIdDifferentUsername;
use tcCore\Rules\SameSchoollocationSameUserNameDifferentExternalId;
use tcCore\Rules\SchoolLocationUserExternalId;
use tcCore\Rules\SchoolLocationUserName;
use tcCore\Rules\UsernameUniqueSchool;
use tcCore\SchoolClass;
use tcCore\Subject;
use tcCore\Traits\UserImporterTrait;

class UserImportRequest extends Request {

    use UserImporterTrait;

    protected $schoolLocation;
    protected $data;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        $this->schoolLocation = Auth::user()->school_location_id;


        return
            Auth::user()->hasRole('School manager') &&
            $this->schoolLocation !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $this->filterInput();
        $extra_rule = [];
        $data  = request()->data;
        foreach ($data as $key => $value) {
            if(is_null(request()->type)){
                break;
            }
            if ($this->hasEntry('username', $value)&&$this->hasEntry('external_id', $value)) {
                if (request()->type == 'teacher') {
                    $extra_rule[sprintf('data.%d.username', $key)] = [  'required',
                                                                        'email:rfc,filter',
                                                                        new UsernameUniqueSchool($this->schoolLocation,request()->type),
                                                                        new SameSchoollocationSameExternalIdDifferentUsername($this->schoolLocation,$value['username'],$value['external_id']),
                                                                        new EmailDns,
                                                                        function ($attribute, $value, $fail) {
                                                                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                                                                return $fail(sprintf('The user email address contains international characters  (%s).', $value));
                                                                            }
                                                                        }];
                    $extra_rule[sprintf('data.%d.external_id', $key)] = new SameSchoollocationSameUserNameDifferentExternalId($this->schoolLocation,$value['username'],false);
                } else {
                    $extra_rule[sprintf('data.%d.external_id', $key)] = ['required',sprintf('unique:users,external_id,%s,username,school_location_id,%d', $value['username'],  $this->schoolLocation)];
                }
            }
        }
        $rules = collect([
            'data.*.username' => ['required','email:rfc,filter',new UsernameUniqueSchool($this->schoolLocation,request()->type),new EmailDns, function ($attribute, $value, $fail) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $fail(sprintf('The user email address contains international characters  (%s).', $value));
                }
            }],
            'data.*.name_first' => 'required',
            'data.*.name' => 'required',
            'data.*.external_id' => 'required',
        ]);

        if ($extra_rule === [] && request()->type == 'teacher') {
            $mergedRules = $rules->merge([
                'data.*.external_id' => '',
            ]);
        }elseif ($extra_rule === []) {
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
    public function sanitize() {
        return $this->all();
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator) {

        $validator->after(function ($validator) {
            if (request()->type == 'teacher') {
                $this->usernameExternalIdCombinationUnique($validator);
            }
            $data = $this->request->all('data');
            $dataCollection = collect(request('data'));
            $unique = collect(request('data'))->unique();
            if ($unique->count() < $dataCollection->count()) {
                $duplicates = $dataCollection->keys()->diff($unique->keys());
                $duplicates->each(function($duplicate) use ($validator) {
                    $validator->errors()->add(
                        sprintf('data.%d.duplicate', $duplicate), 'Dit record komt meerdere keren voor;'
                    );
                });
            }

        });
    }

    private function getSchoolClassByName($school_class_name) {
        return SchoolClass::filtered()->orderBy('created_at', 'desc')->get()->first(function ($school_class) use ($school_class_name) {
            return strtolower($school_class_name) === strtolower($school_class->name);
        });
    }

    private function getSubjectByName($subject_name) {
        return Subject::filtered()->get()->first(function ($subject) use ($subject_name) {
            return strtolower($subject_name) === strtolower($subject->name);
        });
    }

    private function schoolClassYearIsActual($schoolClass){
        $currentYear = SchoolYearRepository::getCurrentSchoolYear();
        return (null !== $currentYear && $currentYear->getKey() === $schoolClass->schoolYear->getKey());
    }

}
