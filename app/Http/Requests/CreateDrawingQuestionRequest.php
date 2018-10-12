<?php namespace tcCore\Http\Requests;

class CreateDrawingQuestionRequest extends CreateQuestionRequest {

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
            'type' => 'required|in:DrawingQuestion',
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
