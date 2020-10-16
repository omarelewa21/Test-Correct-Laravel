<?php namespace tcCore\Http\Requests;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use tcCore\BaseSubject;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\Subject;
use tcCore\User;

class ShowUserRequest extends Request {

	/**
	 * @var User
	 */
	private $user;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
        return true;
	}

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
			$data = ($this->all());
			
			//UUID to ID mapping
            if(isset($data['with']) && isset($data['with']['studentAverageGraph'])){
                $averageGraphSettings = $data['with']['studentAverageGraph'];
                if (array_key_exists('baseSubjectId', $averageGraphSettings)) {
                    if(!Uuid::isValid($averageGraphSettings['baseSubjectId'])){
                        $validator->errors()->add('with','Het basis vak kon helaas niet gevonden worden.');
                    } else {
                        $baseSubject = BaseSubject::whereUuid($averageGraphSettings['baseSubjectId'])->first();
                        if(!$baseSubject){
                            $validator->errors()->add('with','Het basis vak kon helaas niet gevonden worden.');
                        } else {
                            $averageGraphSettings['baseSubjectId'] = $baseSubject->getKey();
                        }
                    }
                }

                if (array_key_exists('subjectId', $averageGraphSettings)) {
                    if(!Uuid::isValid($averageGraphSettings['subjectId'])){
                        $validator->errors()->add('with','Het vak kon helaas niet gevonden worden.');
                    } else {
                        $subject = Subject::whereUuid($averageGraphSettings['subjectId'])->first();
                        if(!$subject){
                            $validator->errors()->add('with','Het vak kon helaas niet gevonden worden.');
                        } else {
                            $averageGraphSettings['subjectId'] = $subject->getKey();
                        }
                    }
                }
                $data['with']['studentAverageGraph'] = $averageGraphSettings;
            }

            if(isset($data['with']) && isset($data['with']['studentPValues'])){
                $studentPValues = $data['with']['studentPValues'];
                if (array_key_exists('baseSubjectId', $studentPValues)) {
                    if(!Uuid::isValid($studentPValues['baseSubjectId'])){
                        $validator->errors()->add('with','Het basis vak kon helaas niet gevonden worden.');
                    } else {
                        $baseSubject = BaseSubject::whereUuid($studentPValues['baseSubjectId'])->first();
                        if(!$baseSubject){
                            $validator->errors()->add('with','Het basis vak kon helaas niet gevonden worden.');
                        } else {
                            $studentPValues['baseSubjectId'] = $baseSubject->getKey();
                        }
                    }
                }

                if (array_key_exists('subjectId', $studentPValues)) {
                    if(!Uuid::isValid($studentPValues['subjectId'])){
                        $validator->errors()->add('with','Het vak kon helaas niet gevonden worden.');
                    } else {
                        $subject = Subject::whereUuid($studentPValues['subjectId'])->first();
                        if(!$subject){
                            $validator->errors()->add('with','Het vak kon helaas niet gevonden worden.');
                        } else {
                            $studentPValues['subjectId'] = $subject->getKey();
                        }
                    }
                }
                $data['with']['studentPValues'] = $studentPValues;
            }


			$this->merge($data);
        });
    }

}
