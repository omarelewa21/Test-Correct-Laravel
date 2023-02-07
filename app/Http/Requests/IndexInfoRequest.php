<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class IndexInfoRequest extends Request
{

    public function authorize()
    {
//        if(!Auth::user()){
//            return false;
//        }
        if(request('mode') === 'feature') {
            return true;
        }

        if(request('mode','dashboard') === 'dashboard'){
            return true;
        } else {
            return Auth::user()->isA('Account manager');
        }

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
