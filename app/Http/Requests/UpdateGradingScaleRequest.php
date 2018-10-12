<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateGradingScaleRequest extends Request {

    /**
     * @var GradingScale
     */
    private $gradingScale;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->gradingScale = $route->getParameter('grading_scale');
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
            'name' => 'sometimes',
            'system_name' => 'sometimes|required|in:OneToTen',
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
