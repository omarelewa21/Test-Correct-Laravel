<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateTestTakeEventRequest extends Request {

    /**
     * @var TestTakeEvent
     */
    private $testTakeEvent;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->testTakeEvent = $route->parameter('test_take_event');
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
            'test_participant_id' => '',
            'test_take_event_type_id' => '',
            'confirmed' => ''
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
