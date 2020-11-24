<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\SchoolLocation;
use tcCore\TestTake;

class StoreSharedSectionSchoolLocationRequest extends Request {

	/**
	 * @var Test
	 */
	private $testTake;

	/**
	 *
	 * @param Route $route
	 */
	function __construct()
	{
        $section = $this->route('section');
        return
            Auth::user()->hasRole('School manager')
            && null !== $section
            && $section->schoolLocations()->where('school_location_id',Auth::user()->schoolLocation->getKey())->count() === 1;
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
		    'school_location_id' => 'required'
		];
	}

	public function prepareForValidation()
    {
        $data = $this->all();

        if($this->has('school_location_id')){
            if(Uuid::isValid($this->get('school_location_id'))){
                $schoolLocation = SchooLlocation::whereUuid($this->get('school_location_id'))->first();
                if(null === $schoolLocation){
                    $this->addPrepareForValidationError('school_location_id','We konden geen bijpassende school locatie vinden.');
                } else {
                    $data['school_location_id'] = $schoolLocation->getKey();
                }
            } else {
                $this->addPrepareForValidationError('school_location_id','We konden geen bijpassende school locatie vinden.');
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
}
