<?php namespace tcCore\Http\Controllers\SchoolLocations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationIp;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateSchoolLocationIpRequest;
use tcCore\Http\Requests\UpdateSchoolLocationIpRequest;

class SchoolLocationIpsController extends Controller {
    /**
     * Display a listing of the school years.
     * @param SchoolLocation $schoolLocation
     * @param Request $request
     * @return
     */
    public function index(SchoolLocation $schoolLocation, Request $request)
    {
        $schoolLocationIps = $schoolLocation->schoolLocationIps()->filtered($request->get('filter', []), $request->get('order', []));
        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($schoolLocationIps->get(), 200);
                break;
            case 'list':
                return Response::make($schoolLocationIps->lists('ip', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($schoolLocationIps->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created school year in storage.
     * @param SchoolLocation $schoolLocation
     * @param CreateSchoolLocationIpRequest $request
     * @return
     */
    public function store(SchoolLocation $schoolLocation, CreateSchoolLocationIpRequest $request)
    {
        $schoolLocationIp = new SchoolLocationIp();

        $schoolLocationIp->fill($request->all());

        if ($schoolLocation->schoolLocationIps()->save($schoolLocationIp) !== false) {
            return Response::make($schoolLocationIp, 200);
        } else {
            return Response::make('Failed to create school location ip', 500);
        }
    }

    /**
     * Display the specified school year.
     * @param SchoolLocation $schoolLocation
     * @param SchoolLocationIp $schoolLocationIp
     * @return
     */
    public function show(SchoolLocation $schoolLocation, SchoolLocationIp $schoolLocationIp)
    {
        if ($schoolLocationIp->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('School location ip not found', 404);
        } else {
            return Response::make($schoolLocationIp, 200);
        }
    }

    /**
     * Update the specified school year in storage.
     * @param SchoolLocation $schoolLocation
     * @param SchoolLocationIp $schoolLocationIp
     * @param UpdateSchoolLocationIpRequest $request
     * @return
     */
    public function update(SchoolLocation $schoolLocation, SchoolLocationIp $schoolLocationIp, UpdateSchoolLocationIpRequest $request)
    {
        $schoolLocationIp->fill($request->all());

        if ($schoolLocation->schoolLocationIps()->save($schoolLocationIp) !== false) {
            return Response::make($schoolLocationIp, 200);
        } else {
            return Response::make('Failed to update school location ip', 500);
        }
    }

    /**
     * Remove the specified school year from storage.
     * @param SchoolLocation $schoolLocation
     * @param SchoolLocationIp $schoolLocationIp
     * @throws \Exception
     * @return
     */
    public function destroy(SchoolLocation $schoolLocation, SchoolLocationIp $schoolLocationIp)
    {
        if ($schoolLocationIp->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('School location ip not found', 404);
        }

        if ($schoolLocationIp->delete()) {
            return Response::make($schoolLocationIp, 200);
        } else {
            return Response::make('Failed to delete school location ip', 500);
        }
    }
}