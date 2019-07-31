<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreateTestQuestionRequest extends CreateQuestionRequest {

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

        $rules['test_id'] = 'required|exists:tests,id,deleted_at,NULL';
        $rules['order'] = 'required|integer|min:0';
        $rules['maintain_position'] = 'required|in:0,1';
        $rules['discuss'] = 'required|in:0,1';

        $rules = $this->removeAttributesThatDontApplyWhenGroupQuestion($rules);

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

    private function removeAttributesThatDontApplyWhenGroupQuestion(array $rules)
    {
        $keys = ['decimal_score', 'score'];

        if (request()->type === 'GroupQuestion') {
            foreach($keys as $key) {
                unset($rules[$key]);
            }
        }

        return $rules;
    }

}
