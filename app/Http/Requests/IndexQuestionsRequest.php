<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\SchoolLocation;
use tcCore\TestTake;

class IndexQuestionsRequest extends Request {


	/**
	 *
	 * @param Route $route
	 */
	function __construct()
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

	public function prepareForValidation()
    {
        $data = $this->all();

        if($this->has('filter')){
            $filter = $this->get('filter');
            if(isset($filter['base_subject_id']) && is_array($filter['base_subject_id'])){
                $baseSubjects = $this->getBaseSubjectKeyArray($filter);
                $this->evaluateBaseSubject($baseSubjects);
                $data['filter']['base_subject_id'] = $baseSubjects;
            }elseif(isset($filter['base_subject_id'])){
                Uuid::isValid($filter['base_subject_id']);
                $baseSubject = BaseSubject::whereUuid($filter['base_subject_id'])->first();
                if($this->evaluateBaseSubject($baseSubject)){
                    $data['filter']['base_subject_id'] = $baseSubject->getKey();
                }
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
    public function withValidator($validator){
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

    private function getBaseSubjectKeyArray($filter)
    {
        $baseSubjects = [];
        foreach ($filter['base_subject_id'] as $key=>$baseSubjectUuid){
            Uuid::isValid($baseSubjectUuid);
            $baseSubject = BaseSubject::whereUuid($filter['base_subject_id'])->first();
            if(is_null($baseSubject)){
                continue;
            }
            $baseSubjects[] = $baseSubject->getKey();
        }
        return $baseSubjects;
    }

    private function evaluateBaseSubject($var)
    {
        if((is_array($var)&&count($var)===0)||is_null($var)){
            $this->addPrepareForValidationError('filter',__('We konden geen bijpassend examenvak vinden.'));
            return false;
        }
        return true;
    }
}
