<?php

namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;

class CreateSearchFilterRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        logger($this);
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'user_id' => Auth::user()->id,
        ]);
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
            'name'=>'required',
            'filters'=>'required',
            'key'=>'required',
            'user_id' => '',
        ];
    }
}
