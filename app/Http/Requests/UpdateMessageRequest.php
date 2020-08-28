<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateMessageRequest extends Request {

    /**
     * @var Message
     */
    private $message;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->message = $route->parameter('message');
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
            'subject' => 'sometimes|required',
            'message' => ''
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
