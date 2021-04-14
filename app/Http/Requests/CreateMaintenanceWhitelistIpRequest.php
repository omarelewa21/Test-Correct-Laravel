<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMaintenanceWhitelistIpRequest extends IndexMaintenanceWhitelistIpRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ip' => 'required',
            'name' => 'required',
        ];
    }
}
