<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateCompletionQuestionAnswerRequest extends Request {

    /**
     * @var CompletionQuestion
     */
    private $completionQuestionAnswer;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->completionQuestionAnswer = $route->parameter('completion_question_answer');
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
            'tag' => '',
            'answer' => ''
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
