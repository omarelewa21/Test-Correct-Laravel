<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateSchoolLocationIpRequest extends Request {

    /**
     * @var SchoolLocationIp
     */
    private $schoolLocationIp;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->schoolLocation = $route->getParameter('school_location_ip');
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
            'name' => ''
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
