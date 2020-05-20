<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\DemoHelper;

class UpdateSectionRequest extends Request {

	/**
	 * @var Section
	 */
	private $section;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->section = $route->parameter('section');
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
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = ($this->all());
            if($data['name'] === DemoHelper::SECTIONNAME){
                if(Auth::user()->schoolLocation->name !== DemoHelper::SCHOOLLOCATIONNAME){
                    $validator->errors()->add('name','Deze naam is helaas niet beschikbaar voor een sectie');
                }
            }
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
