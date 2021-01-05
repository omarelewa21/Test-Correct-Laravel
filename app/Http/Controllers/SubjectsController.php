<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Http\Controllers\Controller;
use tcCore\Subject;
use tcCore\User;
use tcCore\Http\Requests\CreateSubjectRequest;
use tcCore\Http\Requests\UpdateSubjectRequest;

class SubjectsController extends Controller {

    /**
     * Display a listing of the subjects.
     *
     * @return Response
     */
    public function index(Request $request) {
        
        if ($request->mode == 'year') {

            $schoolyear = SchoolYearRepository::getCurrentSchoolYear();
            
            $school_location_id= User::where('id',$request['filter']['user_id'])->value('school_location_id');

            $subjects = Subject::select('subjects.*')
                    ->leftJoin('teachers', 'subjects.id', '=', 'teachers.subject_id')
                    ->leftJoin('school_classes', 'school_classes.id', '=', 'teachers.class_id')
                    ->where('school_classes.school_year_id', $schoolyear->id)
                    ->where('school_classes.school_location_id', $school_location_id)
                    ->orderBy('subjects.name','asc')
                    ->with('baseSubject');

            return Response::make($subjects->distinct()->pluck('name', 'id'), 200);
            
        } else {

            $subjects = Subject::filtered($request->get('filter', []), $request->get('order', ['name' => 'asc']))->with('baseSubject');
        }

        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($subjects->get(), 200);
                break;
            case 'list':
                return Response::make($subjects->pluck('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($subjects->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created subject in storage.
     *
     * @param CreateSubjectRequest $request
     * @return Response
     */
    public function store(CreateSubjectRequest $request) {

        $subject = new Subject($request->all());

        if ($subject->save()) {
            return Response::make($subject, 200);
        } else {
            return Response::make('Failed to create subject', 500);
        }
    }

    /**
     * Display the specified subject.
     *
     * @param  Subject  $subject
     * @return Response
     */
    public function show(Subject $subject) {
        $subject->load('baseSubject');
        return Response::make($subject, 200);
    }

    /**
     * Update the specified subject in storage.
     *
     * @param  Subject $subject
     * @param UpdateSubjectRequest $request
     * @return Response
     */
    public function update(Subject $subject, UpdateSubjectRequest $request) {
        if ($subject->update($request->all())) {
            return Response::make($subject, 200);
        } else {
            return Response::make('Failed to update subject', 500);
        }
    }

    /**
     * Remove the specified subject from storage.
     *
     * @param  Subject  $subject
     * @return Response
     */
    public function destroy(Subject $subject) {
        //
        if ($subject->delete()) {
            return Response::make($subject, 200);
        } else {
            return Response::make('Failed to delete test kind', 500);
        }
    }

}
