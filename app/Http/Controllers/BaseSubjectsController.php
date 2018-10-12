<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\BaseSubject;
//use tcCore\Http\Requests\CreateBaseSubjectRequest;
//use tcCore\Http\Requests\UpdateBaseSubjectRequest;

class BaseSubjectsController extends Controller {

    /**
     * Display a listing of the baseSubjects.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $baseSubjects = BaseSubject::filtered($request->get('filter', []), $request->get('order', []));

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($baseSubjects->get(), 200);
                break;
            case 'list':
                return Response::make($baseSubjects->lists('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($baseSubjects->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created base subject in storage.
     *
     * @param CreateBaseSubjectRequest $request
     * @return Response
     */
    /**
    public function store(CreateBaseSubjectRequest $request)
    {
        //
        $baseSubject = new BaseSubject($request->all());
        if ($baseSubject->save()) {
            return Response::make($baseSubject, 200);
        } else {
            return Response::make('Failed to create base subject', 500);
        }
    }
     */

    /**
     * Display the specified base subject.
     *
     * @param  BaseSubject  $baseSubject
     * @return Response
     */
    /**
    public function show(BaseSubject $baseSubject)
    {
        return Response::make($baseSubject, 200);
    }
     */

    /**
     * Update the specified base subject in storage.
     *
     * @param  BaseSubject $baseSubject
     * @param UpdateBaseSubjectRequest $request
     * @return Response
     */
    /**
    public function update(BaseSubject $baseSubject, UpdateBaseSubjectRequest $request)
    {
        if ($baseSubject->update($request->all())) {
            return Response::make($baseSubject, 200);
        } else {
            return Response::make('Failed to update base subject', 500);
        }
    }
     */

    /**
     * Remove the specified base subject from storage.
     *
     * @param  BaseSubject  $baseSubject
     * @return Response
     */
    /**
    public function destroy(BaseSubject $baseSubject)
    {
        //
        if ($baseSubject->delete()) {
            return Response::make($baseSubject, 200);
        } else {
            return Response::make('Failed to delete base subject', 500);
        }
    }
    */
}
