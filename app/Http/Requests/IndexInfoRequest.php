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
            return Auth::user()->isA('Teacher');
        }

        if(request('mode','dashboard') === 'dashboard'){
            return true;
        }

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
            //
        ];
    }
}
