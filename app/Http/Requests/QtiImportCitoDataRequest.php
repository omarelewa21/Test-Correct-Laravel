<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

class QtiImportCitoDataRequest extends Request {



    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->hasRole('Account manager');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        ];
    }

    public function prepareForValidation()
    {
        // abbreviation field is called abbr on the form ;(
        if (!empty($this->abbr)) {
            $this->merge([
                'abbreviation' => $this->abbr,
            ]);
        }
    }

    /**
     * Get the sanitized input for the request.
     *
     * @return array
     */
    public function sanitize()
    {
        return $this->all();
    }

}
