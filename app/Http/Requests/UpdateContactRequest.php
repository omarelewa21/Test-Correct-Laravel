<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateContactRequest extends Request {

    /**
     * @var Contact
     */
    private $contact;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->contact = $route->getParameter('contact');
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
            'address' => '',
            'postal' => '',
            'city' => '',
            'country' => '',
            'phone' => '',
            'mobile' => ''
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
