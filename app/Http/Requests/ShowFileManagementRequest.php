<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

class ShowFileManagementRequest extends Request {



    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->fileManagement = $this->route('fileManagement');

        return
            Auth::user()->hasRole('Account manager')
            && $this->fileManagement !== null
            && Auth::user()->school_location_id == $this->fileManagement->school_location_id
            && ($this->request->has('type')) ? $this->request->get('type') == $this->fileManagement->type : true;
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
