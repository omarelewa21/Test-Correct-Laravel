<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ShowInfoRequest extends Request
{

    public function authorize()
    {
        $infoModel = $this->route('Info');

        return Auth::user()->isA('Account manager') || (null !== $infoModel && $infoModel->isVisibleForUser(Auth::user()));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
