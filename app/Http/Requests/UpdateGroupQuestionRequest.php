<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\GroupQuestion;

class UpdateGroupQuestionRequest extends UpdateQuestionRequest {

    /**
     * @var GroupQuestion
     */
    private $groupQuestion;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->groupQuestion = $route->getParameter('group_question');
        if ($this->groupQuestion instanceof GroupQuestion) {
            $this->question = $this->groupQuestion->getQuestionInstance();
        }
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
        $baseRules = parent::baseRules();

        return array_merge($baseRules, [
            'type' => 'sometimes|required|in:GroupQuestion',
            'name' => '',
            'shuffle' => '',
            'question' => '',
        ]);
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
