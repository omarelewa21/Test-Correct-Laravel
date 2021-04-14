<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDeploymentRequest extends IndexDeploymentRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'content' => 'required',
            'notification' => 'required',
            'deployment_day' => 'required|date_format:Y-m-d',
            'status' => 'in:PLANNED,NOTIFY,ACTIVE,DONE'
        ];
    }
}
