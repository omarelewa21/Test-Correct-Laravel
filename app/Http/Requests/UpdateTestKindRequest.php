<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateTestKindRequest extends Request {

    /**
     * @var TestKind
     */
    private $testKind;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->testKind = $route->eter('test_kind');
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
            'name' => '',
            'weight' => ''
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
