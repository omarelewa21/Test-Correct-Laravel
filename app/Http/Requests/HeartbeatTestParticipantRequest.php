<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

class HeartbeatTestParticipantRequest extends Request {

    /**
     * @var TestParticipant
     */
    private $testParticipant;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->testParticipant = $route->parameter('test_participant');
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return ($this->testParticipant->user_id == Auth::id());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ip_address' => 'required|ip',
        ];
    }

    /**
     * Get the sanitized input for the request.
     *
     * @return array
     */
    public function sanitize()
    {
        return $this->only('ip_address');
    }

}
