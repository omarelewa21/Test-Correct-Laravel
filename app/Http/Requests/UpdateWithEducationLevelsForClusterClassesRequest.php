<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateWithEducationLevelsForClusterClassesRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::user()->isA('teacher');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'class.*.education_level'      => 'required_with:class.*.checked,education_level_year',
            'class.*.education_level_year' => 'required_with:class.*.checked,education_level',
            //
        ];
    }

    public function messages()
    {
        return [
            'class.*.education_level.required_with'      => __('Niveau is verplicht'),
            'class.*.education_level_year.required_with' => __('Leerjaar is verplicht '),
        ];
    }
}
