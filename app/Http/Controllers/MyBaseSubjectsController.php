<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\BaseSubject;
use tcCore\Http\Requests\AllowOnlyAsTeacherRequest;

//use tcCore\Http\Requests\CreateBaseSubjectRequest;
//use tcCore\Http\Requests\UpdateBaseSubjectRequest;

class MyBaseSubjectsController extends Controller {

    /**
     * Display a listing of the baseSubjects.
     *
     * @return Response
     */
    public function index(AllowOnlyAsTeacherRequest $request)
    {

        $baseSubjects = BaseSubject::whereIn('id',Auth::user()->subjects()->select('base_subject_id'))->orderBy('name');

        switch(strtolower($request->get('mode', 'all'))) {
            case 'all':
                return Response::make($baseSubjects->get(), 200);
                break;
            case 'list':
                return Response::make($baseSubjects->pluck('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($baseSubjects->paginate(15), 200);
                break;
        }
    }


}
