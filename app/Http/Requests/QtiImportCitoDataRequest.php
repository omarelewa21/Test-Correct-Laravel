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
