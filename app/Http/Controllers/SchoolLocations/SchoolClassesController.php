<?php namespace tcCore\Http\Controllers\SchoolLocations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateSchoolClassRequest;
use tcCore\Http\Requests\UpdateSchoolClassRequest;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;

class SchoolClassesController extends Controller {
    /**
     * Display a listing of the school classes.
     * @param SchoolLocation $schoolLocation
     * @param Request $request
     * @return
     */
    public function index(SchoolLocation $schoolLocation, Request $request)
    {
        $schoolClasses = $schoolLocation->schoolClasses()->filtered($request->get('filter', []), $request->get('order', []));
        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($schoolClasses->get(), 200);
                break;
            case 'list':
                return Response::make($schoolClasses->pluck('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($schoolClasses->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created school class in storage.
     * @param SchoolLocation $schoolLocation
     * @param CreateSchoolClassRequest $request
     * @return
     */
    public function store(SchoolLocation $schoolLocation, CreateSchoolClassRequest $request)
    {
        $schoolClass = new SchoolClass();

        $schoolClass->fill($request->all());

        if ($schoolLocation->schoolClasses()->save($schoolClass) !== false) {
            return Response::make($schoolClass, 200);
        } else {
            return Response::make('Failed to create school class', 500);
        }
    }

    /**
     * Display the specified school class.
     * @param SchoolLocation $schoolLocation
     * @param SchoolClass $schoolClass
     * @return
     */
    public function show(SchoolLocation $schoolLocation, SchoolClass $schoolClass)
    {
        if ($schoolClass->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('School class not found', 404);
        } else {
            return Response::make($schoolClass, 200);
        }
    }

    /**
     * Update the specified school class in storage.
     * @param SchoolLocation $schoolLocation
     * @param SchoolClass $schoolClass
     * @param UpdateSchoolClassRequest $request
     * @return
     */
    public function update(SchoolLocation $schoolLocation, SchoolClass $schoolClass, UpdateSchoolClassRequest $request)
    {
        $schoolClass->fill($request->all());

        if ($schoolLocation->schoolClasses()->save($schoolClass) !== false) {
            return Response::make($schoolClass, 200);
        } else {
            return Response::make('Failed to update school class', 500);
        }
    }

    /**
     * Remove the specified school class from storage.
     * @param SchoolLocation $schoolLocation
     * @param SchoolClass $schoolClass
     * @throws \Exception
     * @return
     */
    public function destroy(SchoolLocation $schoolLocation, SchoolClass $schoolClass)
    {
        if ($schoolClass->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('School class not found', 404);
        }

        if ($schoolClass->delete()) {
            return Response::make($schoolClass, 200);
        } else {
            return Response::make('Failed to delete school class', 500);
        }
    }
}