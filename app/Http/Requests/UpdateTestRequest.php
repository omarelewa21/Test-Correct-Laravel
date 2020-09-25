<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\DemoHelper;

class UpdateTestRequest extends Request {

	/**
	 * @var Test
	 */
	private $test;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->test = $route->parameter('test');
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
			'subject_id' => '',
			'education_level_id' => '',
			'period_id' => '',
			'name' => 'sometimes|unique:tests,name,' . $this->test->getKey() . ',id,author_id,' . Auth::id().',deleted_at,NULL,is_system_test,0',
			'abbreviation' => '',
			'kind' => '',
			'status' => '',
			'grade' => '',
			'shuffle' => ''
		];
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
            if(isset($data['name']) && strtolower($data['name']) === strtolower(DemoHelper::BASEDEMOTESTNAME)){
                if(Auth::user()->schoolLocation->name !== DemoHelper::SCHOOLLOCATIONNAME){
                    $validator->errors()->add('name','Deze naam is helaas niet beschikbaar voor een toets');
                }
            }

            if(Uuid::isValid($data['education_level_id'])){
                $educationLevel = EducationLevel::whereUuid($data['education_level_id'])->first();
                if(!$educationLevel){
                    $validator->errors()->add('education_level_id','Dit niveau kon helaas niet terug gevonden worden.');
                } else {
                    $data['education_level_id'] = $educationLevel->getKey();
                }
            }

            request()->merge($data);
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
