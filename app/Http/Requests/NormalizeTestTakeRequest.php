<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\TestTake;

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
        $this->testTake = $route->getParameter('test_take');
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
        $rules = array(
            'ignore_questions' => '',
            'preview' => 'in:0,1'
        );

        if ($this->has('n_term') && $this->has('pass_mark')) {
            $rules['n_term'] = 'required|between:-3.5,5.5';
            $rules['pass_mark'] = 'required|numeric|between:0,100';
        } elseif ($this->has('n_term')) {
            $rules['n_term'] = 'required|between:-3.5,5.5';
        } elseif ($this->has('wanted_average')) {
            $rules['wanted_average'] = 'required|numeric|between:1,10';
        } elseif($this->has('epp')) {
            $rules['epp'] = 'required|numeric|between:0.0001,99.9999';
        } elseif($this->has('ppp') || ($this->testTake->getAttribute('n_term') === null && ($this->testTake->getAttribute('n_term') === null || $this->testTake->getAttribute('pass_mark') === null) && $this->testTake->getAttribute('wanted_average') === null && $this->testTake->getAttribute('epp') === null && $this->testTake->getAttribute('ppp') === null)) {
            $rules['ppp'] = 'required|numeric|between:0.0001,99.9999';
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

}
