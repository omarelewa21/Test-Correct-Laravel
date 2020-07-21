<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

class UpdateFileManagementRequest extends Request {



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
//            && Auth::user()->school_location_id == $this->fileManagement->schoolLocation->getKey()
            && ($this->request->has('type')) ? $this->request->get('type') == $this->fileManagement->type : true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->filterInput();

        return [
            'file_management_status_id' => '',
            'handledby' => '',
            'notes' => '',
            'colorcode' => '',
            'invite' => 'email:rfc,dns',
            'archived' => '',
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
