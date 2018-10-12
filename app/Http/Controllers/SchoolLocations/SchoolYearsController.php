<?php namespace tcCore\Http\Controllers\SchoolLocations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\SchoolLocation;
use tcCore\SchoolYear;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateSchoolYearRequest;
use tcCore\Http\Requests\UpdateSchoolYearRequest;

/**
 * Class SchoolYearsController
 * @package tcCore\Http\Controllers\SchoolLocations
 */
class SchoolYearsController extends Controller {
    /**
     * Display a listing of the school years.
     * @param SchoolLocation $schoolLocation
     * @param Request $request
     * @return
     */
    public function index(SchoolLocation $schoolLocation, Request $request)
    {
        $schoolYears = $schoolLocation->schoolYears()->filtered($request->get('filter', []), $request->get('order', []))->paginate(15);
        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($schoolYears->get(), 200);
                break;
            case 'list':
                return Response::make($schoolYears->lists('year', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($schoolYears->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created school year in storage.
     * @param SchoolLocation $schoolLocation
     * @param CreateSchoolYearRequest $request
     * @return
     */
    public function store(SchoolLocation $schoolLocation, CreateSchoolYearRequest $request)
    {
        $schoolYear = new SchoolYear();

        $schoolYear->fill($request->all());

        if ($schoolLocation->schoolYears()->save($schoolYear) !== false) {
            return Response::make($schoolYear, 200);
        } else {
            return Response::make('Failed to create school year', 500);
        }
    }

    /**
     * Display the specified school year.
     * @param SchoolLocation $schoolLocation
     * @param SchoolYear $schoolYear
     * @return
     */
    public function show(SchoolLocation $schoolLocation, SchoolYear $schoolYear)
    {
        if ($schoolYear->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('School year not found', 404);
        } else {
            return Response::make($schoolYear, 200);
        }
    }

    /**
     * Update the specified school year in storage.
     * @param SchoolLocation $schoolLocation
     * @param SchoolYear $schoolYear
     * @param UpdateSchoolYearRequest $request
     * @return
     */
    public function update(SchoolLocation $schoolLocation, SchoolYear $schoolYear, UpdateSchoolYearRequest $request)
    {
        $schoolYear->fill($request->all());

        if ($schoolLocation->schoolYears()->save($schoolYear) !== false) {
            return Response::make($schoolYear, 200);
        } else {
            return Response::make('Failed to update school year', 500);
        }
    }

    /**
     * Remove the specified school year from storage.
     * @param SchoolLocation $schoolLocation
     * @param SchoolYear $schoolYear
     * @throws \Exception
     * @return
     */
    public function destroy(SchoolLocation $schoolLocation, SchoolYear $schoolYear)
    {
        if ($schoolYear->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('School year not found', 404);
        }

        if ($schoolYear->delete()) {
            return Response::make($schoolYear, 200);
        } else {
            return Response::make('Failed to delete school year', 500);
        }
    }
}