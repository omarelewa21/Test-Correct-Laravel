<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\CreateSchoolYearRequest;
use tcCore\Http\Requests\UpdateSchoolYearRequest;
use tcCore\SchoolYear;

class SchoolYearsController extends Controller
{

    /**
     * Display a listing of the school years.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $schoolYears = SchoolYear::filtered($request->get('filter', []), $request->get('order', []))->with('schoolLocations');
        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($schoolYears->get(), 200);
                break;
            case 'list':
                return Response::make($schoolYears->pluck('year', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($schoolYears->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created school year in storage.
     *
     * @param CreateSchoolYearRequest $request
     * @return Response
     */
    public function store(CreateSchoolYearRequest $request)
    {
        $schoolYear = new SchoolYear();

        $schoolYear->fill($request->all());

        if ($schoolYear->save()) {
            return Response::make($schoolYear, 200);
        } else {
            return Response::make('Failed to create school year', 500);
        }
    }

    /**
     * Display the specified school year.
     *
     * @param  SchoolYear $schoolYear
     * @return Response
     */
    public function show(SchoolYear $schoolYear)
    {
        $schoolYear->load('periods', 'schoolLocations');
        return Response::make($schoolYear, 200);
    }

    /**
     * Update the specified school year in storage.
     *
     * @param  SchoolYear $schoolYear
     * @param UpdateSchoolYearRequest $request
     * @return Response
     */
    public function update(SchoolYear $schoolYear, UpdateSchoolYearRequest $request)
    {
        $schoolYear->fill($request->all());
        if ($schoolYear->save()) {
            return Response::make($schoolYear, 200);
        } else {
            return Response::make('Failed to update school year', 500);
        }
    }

    /**
     * Remove the specified school year from storage.
     *
     * @param  SchoolYear $schoolYear
     * @return Response
     */
    public function destroy(SchoolYear $schoolYear)
    {
        if ($schoolYear->delete()) {
            return Response::make($schoolYear, 200);
        } else {
            return Response::make('Failed to delete school year', 500);
        }
    }

    /**
     * Returns an id and name-array for a select-box.
     *
     * @return Response
     */
    public function lists()
    {
        return Response::make(SchoolYear::orderBy('year', 'asc')->pluck('year', 'id'));
    }
}
