<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UpdateTestQuestionRequest extends UpdateQuestionRequest {

    /**
     * @var TestQuestion
     */
    protected $testQuestion;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->testQuestion = $route->getParameter('test_question');
        $this->question = $this->testQuestion->question;
        $this->route = $route;
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
        $rules = parent::rules();

        $rules['question_id'] = 'sometimes|question_id';
        $rules['test_id'] = 'sometimes|exists:tests,id,deleted_at,NULL';
        $rules['order'] = 'sometimes|required|integer|min:0';
        $rules['maintain_position'] = 'sometimes|required|in:0,1';
        $rules['discuss'] = 'sometimes|required|in:0,1';

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
