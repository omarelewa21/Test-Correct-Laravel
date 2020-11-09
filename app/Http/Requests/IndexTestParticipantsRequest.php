<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\SchoolLocation;
use tcCore\TestTake;

class IndexTestParticipantsRequest extends Request {

	/**
	 * @var Test
	 */
	private $testTake;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
        $this->testTake = $this->route('testTake');
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

        if($this->has('from_retake') && $this->input('from_retake') == true){
            $_retakeTestTake = $this->testTake->retakeTestTake;
            if(null !== $_retakeTestTake){
                $data['retakeTestTake'] = $_retakeTestTake;
            } else {
                $this->addPrepareForValidationError('test_take','We konden geen retake vinden die hierbij hoort.');
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
