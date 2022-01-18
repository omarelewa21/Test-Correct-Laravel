<?php namespace tcCore\Http\Requests;

use tcCore\Rules\GroupQuestionAudioAttachment;

class CreateAttachmentRequest extends Request {

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
        if(!is_null(request()->route('test_question'))){
            return [
                'json' => ['sometimes',new GroupQuestionAudioAttachment(request()->type,request()->title)]
            ];
        }
		return [
			//
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
