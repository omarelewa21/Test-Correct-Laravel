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
use tcCore\Rules\TeacherWithSchoolClassAndSubjectShouldNotExist;
use tcCore\Rules\TeacherWithSubjectShouldNotExist;
use tcCore\Rules\UsernameExternalIdCombinationUniqueRule;
use tcCore\Rules\UsernameUniqueSchool;
use tcCore\SchoolClass;
use tcCore\Subject;
use tcCore\Traits\UserImporterTrait;

class TeachersImportRequest extends Request {

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
            if ($this->hasEntry('username', $value)&&$this->hasEntry('external_id', $value)) {
                $extra_rule[sprintf('data.%d.username', $key)] = [  'required',
                    'email:rfc,filter',
                    new UsernameUniqueSchool($this->schoolLocation,'teacher'),
                    new SameSchoollocationSameExternalIdDifferentUsername($this->schoolLocation,$value['username'],$value['external_id']),
                    new EmailDns,
                    function ($attribute, $value, $fail) {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            return $fail(sprintf('The user email address contains international characters  (%s).', $value));
                        }
                    }];
                $extra_rule[sprintf('data.%d.external_id', $key)] = new SameSchoollocationSameUserNameDifferentExternalId($this->schoolLocation,$value['username'],true);
            }
            if ($this->hasEntry('username', $value)&&$this->hasEntry('school_class',$value)&&$this->hasEntry('subject',$value)) {
                $extra_rule[sprintf('data.%d.subject', $key)] = [
                        new TeacherWithSchoolClassAndSubjectShouldNotExist($this->schoolLocation,$value),
                ];
            }
            if ($this->hasEntry('username', $value)&&!$this->hasEntry('external_id', $value)) {
                $extra_rule[sprintf('data.%d.username', $key)] = [  'required',
                    'email:rfc,filter',
                    new UsernameUniqueSchool($this->schoolLocation,'teacher'),
                    new EmailDns,
                    function ($attribute, $value, $fail) {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            return $fail(sprintf('The user email address contains international characters  (%s).', $value));
                        }
                    }];
            }
        }
        $rules = collect([
            'data.*.username' => ['required', 'email:rfc,filter',new UsernameUniqueSchool($this->schoolLocation,'teacher'),new EmailDns, function ($attribute, $value, $fail) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $fail(sprintf('The user email address contains international characters  (%s).', $value));
                }
            }],
            'data.*.name_first' => 'required',
            'data.*.name' => 'required',
            'data.*.school_class' => 'required',
            'data.*.subject' => 'required',
        ]);
        if ($extra_rule === []) {
            $mergedRules = $rules;
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
     * @noinspection UnsupportedStringOffsetOperationsInspection
     */
    public function withValidator($validator) {
        $validator->after(function ($validator) {
            $data = $this->request->get('data');
            $teachers = collect(request('data'))->map(function ($row, $index) use ($validator, &$data) {
                if (!array_key_exists('school_class', $row)) {

                } else {
                    $schoolClass = $this->getSchoolClassByName($row['school_class']);

                    if ($schoolClass === null) {
                        $validator->errors()->add(
                                sprintf('data.%d.school_class', $index), 'de opgegeven klas dient in de database aanwezig te zijn voor deze schoollocatie'
                        );
                    } else if(!$this->schoolClassYearIsActual($schoolClass)){
                        $validator->errors()->add(
                            sprintf('data.%d.school_class', $index),
                            'de opgegeven klas is niet aanwezig voor dit schooljaar ('.$schoolClass->schoolYear->year.')'

                        );
                    } else {

                        $data[$index]['class_id'] = $schoolClass->getKey();
                    }
                }
                if (!array_key_exists('subject', $row)) {

                } else {
                    $subject = $this->getSubjectByName($row['subject']);
                    if ($subject == null) {
                        $validator->errors()->add(
                                sprintf('data.%d.subject', $index), 'het opgegeven vak dient in de database aanwezig te zijn voor deze schoollocatie'
                        );
                    } else {
                        $data[$index]['subject_id'] = $subject->getKey();
                    }
                }
            });
            $this->merge(['data' => $data]);

            $this->usernameExternalIdSchoolClassSubjectUniqueInImport($validator);
            $this->usernameExternalIdCombinationUnique($validator);



            //$unique = collect(request('data'))->unique();
//            if ($unique->count() < $dataCollection->count()) {
//                $duplicates = $dataCollection->keys()->diff($unique->keys());
//                $duplicates->each(function($duplicate) use ($validator) {
//                    $validator->errors()->add(
//                            sprintf('data.%d.duplicate', $duplicate), 'Dit record komt meerdere keren voor;'
//                    );
//                });
//            }
        });
    }

//    private function usernameExternalIdSchoolClassSubjectUniqueInImport(&$validator)
//    {
//        $dataCollection = collect(request('data'));
//        $essentialDataCollection = $dataCollection->map(function ($item, $key) {
//            $username = array_key_exists('username',$item)?$item['username']:'';
//            $external_id = array_key_exists('external_id',$item)?$item['external_id']:'';
//            $school_class = array_key_exists('school_class',$item)?$item['school_class']:'';
//            $subject = array_key_exists('subject',$item)?$item['subject']:'';
//            return [    'username' => $username,
//                        'external_id' => $external_id,
//                        'school_class' => $school_class,
//                        'subject' => $subject,
//                    ];
//        });
//        $unique = $essentialDataCollection->unique();
//        if ($unique->count() < $essentialDataCollection->count()) {
//            $duplicates = $essentialDataCollection->keys()->diff($unique->keys());
//            $duplicates->each(function($duplicate) use ($validator) {
//                $validator->errors()->add(
//                    sprintf('data.%d.duplicate', $duplicate), 'Dit record komt meerdere keren voor;'
//                );
//            });
//        }
//    }
//
//    private function usernameExternalIdCombinationUnique(&$validator)
//    {
//        $dataCollection = collect(request('data'));
//        $usernameDataCollection = $dataCollection->pluck('username');
//        $externalIdDataCollection = $dataCollection->pluck(['external_id']);
//        $usernameExternalIdDataCollection = $dataCollection->map(function ($item, $key) {
//            $username = array_key_exists('username',$item)?$item['username']:'';
//            $external_id = array_key_exists('external_id',$item)?$item['external_id']:'';
//            return [    'username' => $username,
//                        'external_id' => $external_id,
//            ];
//        });
//        $this->validateUsernameExternalIdCombination($validator,$usernameDataCollection,$usernameExternalIdDataCollection,'username','external_id');
//        $this->validateUsernameExternalIdCombination($validator,$externalIdDataCollection,$usernameExternalIdDataCollection,'external_id','username');
//
//    }
//
//    private function getSchoolClassByName($school_class_name) {
//        return SchoolClass::filtered()->orderBy('created_at', 'desc')->get()->filter(function ($school_class) use ($school_class_name) {
//                    return strtolower($school_class_name) === strtolower($school_class->name);
//                })->first();
//    }
//
//    private function getSubjectByName($subject_name) {
//        return Subject::filtered()->get()->filter(function ($subject) use ($subject_name) {
//                    return strtolower($subject_name) === strtolower($subject->name);
//                })->first();
//    }
//
//    private function schoolClassYearIsActual($schoolClass){
//        $currentYear = SchoolYearRepository::getCurrentSchoolYear();
//        return (null !== $currentYear && $currentYear->getKey() === $schoolClass->schoolYear->getKey());
//    }
//
//    private function validateUsernameExternalIdCombination(&$validator,$paramCollection,$usernameExternalIdDataCollection,$primaryParamName,$secondaryParamName)
//    {
//        $unique = $paramCollection->unique();
//        $duplicates = $paramCollection->diffAssoc($unique);
//        $duplicates->each(function($duplicate,$duplicatekey) use ($validator,$usernameExternalIdDataCollection,$primaryParamName,$secondaryParamName) {
//            $firstEntry = $usernameExternalIdDataCollection->where($primaryParamName,$duplicate)->first();
//            $secondaryParam = $firstEntry[$secondaryParamName];
//            $filtered = $usernameExternalIdDataCollection->filter(function ($value, $key) use ($duplicate,$primaryParamName) {
//                return $value[$primaryParamName] == $duplicate;
//            });
//            foreach ($filtered as $entry){
//                if($entry[$secondaryParamName]!=$secondaryParam){
//
//                    $validator->errors()->add(
//                        sprintf('data.%d.duplicate', $duplicatekey), sprintf('this record occurs multiple times with %s combination',$secondaryParamName)
//                    );
//                }
//
//            }
//        });
//    }

}
