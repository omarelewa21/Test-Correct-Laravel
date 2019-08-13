<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use tcCore\DrawingQuestion;

class UpdateDrawingQuestionRequest extends UpdateQuestionRequest {

    /**
     * @var DrawingQuestion
     */
    private $drawingQuestion;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route)
    {
        $this->drawingQuestion = $route->parameter('drawing_question');
        if ($this->drawingQuestion instanceof DrawingQuestion) {
            $this->question = $this->drawingQuestion->getQuestionInstance();
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
            'type' => 'sometimes|required|in:DrawingQuestion',
            'answer' => '',
            'bg' => '',
            'grid' => ''
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
