<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use tcCore\Rules\ValidWebDomain;

class RttiImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->hasRole('Administrator');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'csv_file' => 'required',
            'separator' => 'required',
            'email_domain' => ['required',new ValidWebDomain()],
        ];
    }

    public function messages()
    {
        return [
            'csv_file.required' => 'Een csv bestand is verplicht',
            'separator.required' => 'Separator is verplicht, kies de juiste separator',
            'email_domain.required' => 'Domein is verplicht, kies een valide domein',
        ];
    }

}
