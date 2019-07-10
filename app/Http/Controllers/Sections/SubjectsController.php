<?php namespace tcCore\Http\Controllers\Sections;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Subject;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateSubjectRequest;
use tcCore\Http\Requests\UpdateSubjectRequest;
use tcCore\Section;

class SubjectsController extends Controller {
    /**
     * Display a listing of the subjects.
     * @param Section $section
     * @param Request $request
     * @return
     */
    public function index(Section $section, Request $request)
    {
        $subjects = $section->subjects()->filtered($request->get('filter', []), $request->get('order', []))->with('baseSubject');

        switch(strtolower($request->get('mode', 'paginate'))) {
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
     * @param Section $section
     * @param CreateSubjectRequest $request
     * @return
     */
    public function store(Section $section, CreateSubjectRequest $request)
    {
        $subject = new Subject();

        $subject->fill($request->all());

        if ($section->subjects()->save($subject) !== false) {
            return Response::make($subject, 200);
        } else {
            return Response::make('Failed to create subject', 500);
        }
    }

    /**
     * Display the specified subject.
     * @param Section $section
     * @param Subject $subject
     * @return
     */
    public function show(Section $section, Subject $subject)
    {
        $subject->load('baseSubject');
        if ($subject->school_location_id !== $section->getKey()) {
            return Response::make('Subject not found', 404);
        } else {
            return Response::make($subject, 200);
        }
    }

    /**
     * Update the specified subject in storage.
     * @param Section $section
     * @param Subject $subject
     * @param UpdateSubjectRequest $request
     * @return
     */
    public function update(Section $section, Subject $subject, UpdateSubjectRequest $request)
    {
        $subject->fill($request->all());

        if ($section->subjects()->save($subject) !== false) {
            return Response::make($subject, 200);
        } else {
            return Response::make('Failed to update subject', 500);
        }
    }

    /**
     * Remove the specified subject from storage.
     * @param Section $section
     * @param Subject $subject
     * @throws \Exception
     * @return
     */
    public function destroy(Section $section, Subject $subject)
    {
        if ($subject->school_location_id !== $section->getKey()) {
            return Response::make('Subject not found', 404);
        }

        if ($subject->delete()) {
            return Response::make($subject, 200);
        } else {
            return Response::make('Failed to delete subject', 500);
        }
    }
}