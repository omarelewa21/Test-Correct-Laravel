<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\SchoolLocation;

class UpdateSchoolLocationRequest extends Request {

	/**
	 * @var SchoolLocation
	 */
	private $schoolLocation;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->schoolLocation = $route->parameter('school_location');
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
		return [
			'name' => ''
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
            if(isset($data['customer_code'])){
                $schoolLocation = SchoolLocation::where('customer_code',$data['customer_code'])->first();
                if($schoolLocation && $schoolLocation->getKey() != $this->schoolLocation->getKey()) {
                    $validator->errors()->add('customer_code', 'Er bestaat al een school locatie met dit klantnummer');
                }
            }
        });
    }

}
