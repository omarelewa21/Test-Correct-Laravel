<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\DemoHelper;

class UpdateSubjectRequest extends Request {

	/**
	 * @var Subject
	 */
	private $subject;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->subject = $route->parameter('subject');
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
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = ($this->all());
            if(isset($data['name']) && strtolower($data['name']) === strtolower(DemoHelper::SUBJECTNAME)){
                if(Auth::user()->schoolLocation->name !== DemoHelper::SCHOOLLOCATIONNAME){
                    $validator->errors()->add('name','Deze naam is helaas niet beschikbaar voor een vak');
                }
            }
        });
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

}
