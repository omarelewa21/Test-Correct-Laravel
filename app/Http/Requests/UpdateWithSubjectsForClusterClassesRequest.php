<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateWithSubjectsForClusterClassesRequest extends FormRequest
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
            //
        ];
    }

    public function messages()
    {
        return [
//            'class.*.education_level.required_with' => __('Niveau is verplicht als u gecontroleerd aanvinkt'),
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $teacher = $this->get('teacher', []);
            $checked = $this->get('class', []);

            foreach ($checked as $key => $arrayChecked) {
                if (strtolower($arrayChecked['checked']) == 'on') {
                    if (!array_key_exists($key, $teacher)) {
                        $validator->errors()->add('teacher.'.$key, __('Het is verplicht om een vak te kiezen'));
                    }
                }
            }
        });
    }
}
