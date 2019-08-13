<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateAddressRequest extends Request {

    /**
     * @var Address
     */
    private $address;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->address = $route->parameter('address');
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
            'address' => '',
            'postal' => '',
            'city' => '',
            'country' => ''
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
