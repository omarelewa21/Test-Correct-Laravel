<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UpdateGroupQuestionQuestionRequest extends UpdateQuestionRequest {

    /**
     * @var GroupQuestionQuestion
     */
    protected $groupQuestionQuestion;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->groupQuestionQuestion = $route->getParameter('group_question_question_id');
        $this->question = $this->groupQuestionQuestion->question;
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

        $rules['group_question_id'] = 'sometimes|exists:group_question_questions,id,deleted_at,NULL';
        $rules['order'] = 'sometimes|required|integer|min:0';
        $rules['maintain_position'] = 'sometimes|required|in:0,1';

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
