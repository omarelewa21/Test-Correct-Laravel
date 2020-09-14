<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use tcCore\SchoolClass;
use tcCore\Subject;

class TeachersImportRequest extends Request
{

    protected $schoolLocation;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
    public function rules()
    {
        $this->filterInput();

        return [
//		    'data.*' => 'distinct',
            'data.*.username'     => 'required|email',
            'data.*.name_first'   => 'required',
            'data.*.name'         => 'required',
            'data.*.school_class' => 'required',
            'data.*.subject'      => 'required',
//            'data.*.class_id'     => 'required|sometimes',
//            'data.*.subject_id'   => 'required|sometimes',
        ];
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
            $data = $this->request->get('data');
            $teachers = collect(request('data'))->map(function ($row, $index) use ($validator, &$data) {
                if (!array_key_exists('school_class', $row)) {

                } else {
                    $schoolClass = $this->getSchoolClassByName($row['school_class']);

                    if ($schoolClass === null) {
                        $validator->errors()->add(
                            sprintf('data.%d.school_class', $index),
                            'de opgegeven klas dient in de database aanwezig te zijn voor deze schoollocatie'
                        );
                    } else if($schoolClass->schoolYear->year != date('Y')){
                        $validator->errors()->add(
                            sprintf('data.%d.school_class', $index),
                            'de opgegeven klas is niet aanwezig voor dit schooljaar ('.date('Y').')'
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
                            sprintf('data.%d.subject', $index),
                            'het opgegeven vak dient in de database aanwezig te zijn voor deze schoollocatie'
                        );
                    } else {
                        $data[$index]['subject_id'] = $subject->getKey();
                    }
                }
            });
            request()->merge(['data' => $data]);

            $dataCollection = collect(request('data'));
            $unique = collect(request('data'))->unique();
            if($unique->count() < $dataCollection->count()) {
                $duplicates = $dataCollection->keys()->diff($unique->keys());
                $duplicates->each(function($duplicate) use ($validator) {
                    $validator->errors()->add(
                        sprintf('data.%d.duplicate', $duplicate),
                        'Dit record komt meerdere keren voor;'
                    );
                });
            }
        });
    }

    private function getSchoolClassByName($school_class_name)
    {
        return SchoolClass::filtered()->orderBy('created_at','desc')->get()->first(function ($school_class) use ($school_class_name) {
            return strtolower($school_class_name) === strtolower($school_class->name);
        });
    }

    private function getSubjectByName($subject_name)
    {
        return Subject::filtered()->get()->first(function ($subject) use ($subject_name) {
            return strtolower($subject_name) === strtolower($subject->name);
        });
    }

}
