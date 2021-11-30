<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteInfoRequest extends IndexDeploymentRequest
{
    public function authorize()
    {
        return Auth::user()->isA('Account manager');
    }
}
