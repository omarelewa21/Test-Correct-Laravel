<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use tcCore\Info;

class CreateInfoRequest extends Request
{
    public function authorize()
    {
        return Auth::user()->isA('Account manager');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title_nl'   => 'required',
            'title_en'   => 'required',
            'content_nl' => 'required',
            'content_en' => 'required',
            'status'     => 'required|in:' . Info::ACTIVE . ',' . Info::INACTIVE,
            'type'       => 'required|in:' . Info::BASE_TYPE . ',' . Info::FEATURE_TYPE,
            'show_from'  => 'required',
            'show_until' => 'required',
            'for_all'    => '',
            'roles'      => ''
        ];
    }
}
