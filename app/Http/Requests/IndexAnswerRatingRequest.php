<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\TestTake;

class IndexAnswerRatingRequest extends Request {

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
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = ($this->all());
            if(isset($data['filter']) && isset($data['filter']['test_take_id']) && Uuid::isValid($data['filter']['test_take_id'])){
                $testTake = TestTake::whereUuid($data['filter']['test_take_id'])->first();
                if(!$testTake){
                    $validator->errors()->add('test_take_id','De toetsafname kon niet gevonden worden');
                } else {
                    $data['filter']['test_take_id'] = $testTake->getKey();
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
