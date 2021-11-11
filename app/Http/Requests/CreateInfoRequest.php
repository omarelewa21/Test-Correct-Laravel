<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use tcCore\Info;

class CreateInfoRequest extends IndexDeploymentRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title_nl' => 'required',
            'title_en' => 'required',
            'content_nl' => 'required',
            'content_en' => 'required',
            'status' => 'required|in:'.Info::ACTIVE.','.Info::INACTIVE,
            'show_from' => 'required',
            'show_until'=> 'required',
        ];
    }
}
