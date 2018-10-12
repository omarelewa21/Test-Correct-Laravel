<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Validator;

class CreateGroupQuestionQuestionRequest extends CreateQuestionRequest {

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

        if ($this->validateQuestionId()) {
            $rules = ['question_id' => ''];
        } else {
            $rules = parent::rules();
        }

        $rules['order'] = 'required|integer|min:0';
        $rules['maintain_position'] = 'required|in:0,1';

        return $rules;
    }

    public function validateQuestionId() {
        $validator = Validator::make(
            ['question_id' => $this->input('question_id')],
            ['question_id' => 'exists:questions,id,deleted_at,NULL']
        );

        return !$validator->fails();
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
