<?php namespace tcCore\Http\Requests;

use tcCore\TestTakeEventType;

class CreateTestTakeEventRequest extends Request {

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
            'test_participant_id' => '',
            'test_take_event_type_id' => '',
            'confirmed' => '',
            'reason' => ''
        ];
    }

    public function prepareForValidation()
    {
        $data = $this->all();

        if (!isset($data['reason'])) {
            return;
        }
        $key = TestTakeEventType::where("reason", "=", $data['reason'])->first()->getKey();

        if ($key != null) {
            $data['test_take_event_type_id'] = $key;
            unset($data['reason']);
        } else {
            $data['test_take_event_type_id'] = 10;
            unset($data['reason']);
        }
        
        $this->merge($data);
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
