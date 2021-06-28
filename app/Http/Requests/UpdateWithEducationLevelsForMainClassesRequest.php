<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateWithEducationLevelsForMainClassesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // @TODO @martin do we need the account maanger here??
        return Auth::user()->isA('teacher') || Auth::user()->isA('Account manager') || Auth::user()->isA('School manager');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'class.*.education_level' => 'required_with:class.*.checked',
            //
        ];
    }

    public function messages() {
        return [
            'class.*.education_level.required_with' => __('Niveau is verplicht als u gecontroleerd aanvinkt'),
        ];
    }
}
