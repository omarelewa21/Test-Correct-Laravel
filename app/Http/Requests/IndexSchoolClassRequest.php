<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\SchoolLocation;
use tcCore\TestTake;

class IndexSchoolClassRequest extends Request {

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
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator){
        $validator->after(function ($validator) {
            $data = $this->all();

            if(isset($data['filter']) && isset($data['filter']['school_location_id']) && Uuid::isValid($data['filter']['school_location_id'])){
                $item = SchoolLocation::whereUuid($data['filter']['school_location_id'])->first();
                if(!$item){
                    $validator->errors()->add('school_location_id','De school locatie kon niet gevonden worden.');
                } else {
                    $data['filter']['school_location_id'] = $item->getKey();
                }
            }
            $this->merge($data);
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
