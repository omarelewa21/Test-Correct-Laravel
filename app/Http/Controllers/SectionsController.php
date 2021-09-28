<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\CreateSectionRequest;
use tcCore\Http\Requests\UpdateSectionRequest;
use tcCore\SchoolLocation;
use tcCore\Section;
use tcCore\Subject;
use tcCore\BaseSubject;

class SectionsController extends Controller {
    /**
     * Display a listing of the sections.
     * @param SchoolLocation $schoolLocation
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $_schoolSections = Section::filtered($request->get('filter', []), $request->get('order', []))->with('schoolLocations');
        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($_schoolSections->get(), 200);
                break;
            case 'list':
                return Response::make($_schoolSections->pluck('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                $_schoolSections->with('schoolLocations','sharedSchoolLocations','subjects','subjects.baseSubject');
                $schoolSections = $_schoolSections->paginate(15);
                return Response::make($schoolSections, 200);
                break;
            }
        }


     /**
     * Store a newly created sections in storage.
     * @param CreateSectionRequest $request
     * @return
     */
    public function store(CreateSectionRequest $request)
    {
        $section = new Section();

        $section->fill($request->all());

        if ($section->save() !== false) {
            return Response::make($section, 200);
        } else {
            return Response::make('Failed to create section', 500);
        }
    }

    /**
     * Display the specified section.
     * @param Section $section
     * @return Response
     */
    public function show(Section $section)
    {
        $section->load('subjects', 'subjects.baseSubject', 'schoolLocations','subjects.teachers', 'subjects.teachers.user');
        return Response::make($section, 200);
    }

    /**
     * Update the specified section in storage.
     * @param Section $section
     * @param UpdateSectionRequest $request
     * @return Response
     */
    public function update(Section $section, UpdateSectionRequest $request)
    {
        $section->fill($request->all());

        if ($section->save() !== false) {
            return Response::make($section, 200);
        } else {
            return Response::make('Failed to update section', 500);
        }
    }

    /**
     * Remove the specified section from storage.
     * @param Section $section
     * @throws \Exception
     * @return Response
     */
    public function destroy(Section $section)
    {
        if ($section->delete()) {
            return Response::make($section, 200);
        } else {
            return Response::make('Failed to delete section', 500);
        }
    }
}