<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\TestTake;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class NormalizeTestTakeRequest extends Request {

    /**
     * @var TestTake
     */
    private $testTake;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->testTake = $route->parameter('test_take');
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

        $rules = array(
            'ignore_questions' => '',
            'preview' => 'in:0,1'
        );

        if( $this->has('ignore_questions') ){
            $max = $this->testTake->maxScore($this->get('ignore_questions'));
        }else{
            $max = $this->testTake->maxScore();
        }

        if ($this->has('n_term') && $this->has('pass_mark')) {
            $rules['n_term'] = 'required|numeric|between:0,10';
            $rules['pass_mark'] = 'required|numeric|between:0,100';
        } elseif ($this->has('n_term')) {
            $rules['n_term'] = 'required|numeric|between:0,10';
        } elseif ($this->has('wanted_average')) {
            $rules['wanted_average'] = 'required|numeric|between:1,10';
        } elseif($this->has('epp')) {
            $rules['epp'] = 'required|numeric|min:0.0001|max:' . $max;
        } elseif($this->has('ppp') || ($this->testTake->getAttribute('n_term') === null && ($this->testTake->getAttribute('n_term') === null || $this->testTake->getAttribute('pass_mark') === null) && $this->testTake->getAttribute('wanted_average') === null && $this->testTake->getAttribute('epp') === null && $this->testTake->getAttribute('ppp') === null)) {
            $rules['ppp'] = 'required|numeric|min:0.0001|max:' . $max;
        }

        return $rules;
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

    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(
            response()->json(['status' => 0,'message' => $validator->errors()->all()], 422)
        );
    }

}
