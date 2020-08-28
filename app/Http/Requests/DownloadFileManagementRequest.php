<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

class DownloadFileManagementRequest extends Request {



    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
//        $this->schoolLocation = $this->route('schoolLocation');
        $this->fileManagement = $this->route('fileManagement');

        return
            Auth::user()->hasRole('Account manager')
//            && $this->schoolLocation !== null
//            && Auth::user()->school_location_id == $this->schoolLocation->getKey()
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
