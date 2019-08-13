<?php namespace tcCore\Http\Controllers\SchoolLocations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\SchoolLocation;
use tcCore\Section;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateSectionRequest;
use tcCore\Http\Requests\UpdateSectionRequest;

class SectionsController extends Controller {
    /**
     * Display a listing of the sections.
     * @param SchoolLocation $schoolLocation
     * @param Request $request
     * @return Response
     */
    public function index(SchoolLocation $schoolLocation, Request $request)
    {
        $schoolSections = $schoolLocation->sections()->filtered($request->get('filter', []), $request->get('order', []))->paginate(15);
        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($schoolSections->get(), 200);
                break;
            case 'list':
                return Response::make($schoolSections->pluck('year', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($schoolSections->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created sections in storage.
     * @param SchoolLocation $schoolLocation
     * @param CreateSectionRequest $request
     * @return
     */
    public function store(SchoolLocation $schoolLocation, CreateSectionRequest $request)
    {
        $section = new Section();

        $section->fill($request->all());

        if ($schoolLocation->sections()->save($section) !== false) {
            return Response::make($section, 200);
        } else {
            return Response::make('Failed to create section', 500);
        }
    }

    /**
     * Display the specified section.
     * @param SchoolLocation $schoolLocation
     * @param Section $section
     * @return Response
     */
    public function show(SchoolLocation $schoolLocation, Section $section)
    {
        if ($section->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('Section not found', 404);
        } else {
            return Response::make($section, 200);
        }
    }

    /**
     * Update the specified section in storage.
     * @param SchoolLocation $schoolLocation
     * @param Section $section
     * @param UpdateSectionRequest $request
     * @return Response
     */
    public function update(SchoolLocation $schoolLocation, Section $section, UpdateSectionRequest $request)
    {
        $section->fill($request->all());

        if ($schoolLocation->sections()->save($section) !== false) {
            return Response::make($section, 200);
        } else {
            return Response::make('Failed to update section', 500);
        }
    }

    /**
     * Remove the specified section from storage.
     * @param SchoolLocation $schoolLocation
     * @param Section $section
     * @throws \Exception
     * @return Response
     */
    public function destroy(SchoolLocation $schoolLocation, Section $section)
    {
        if ($section->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('Section not found', 404);
        }

        if ($schoolLocation->delete()) {
            return Response::make($section, 200);
        } else {
            return Response::make('Failed to delete section', 500);
        }
    }
}