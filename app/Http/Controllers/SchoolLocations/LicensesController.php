<?php namespace tcCore\Http\Controllers\SchoolLocations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\License;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateLicenseRequest;
use tcCore\Http\Requests\UpdateLicenseRequest;
use tcCore\SchoolLocation;

class LicensesController extends Controller {
    /**
     * Display a listing of the licenses.
     * @param SchoolLocation $schoolLocation
     * @param Request $request
     * @return
     */
    public function index(SchoolLocation $schoolLocation, Request $request)
    {
        $licenses = $schoolLocation->licenses()->filtered($request->get('filter', []), $request->get('order', []));
        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($licenses->get(), 200);
                break;
            case 'list':
                return Response::make($licenses->lists('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($licenses->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created license in storage.
     * @param SchoolLocation $schoolLocation
     * @param CreateLicenseRequest $request
     * @return
     */
    public function store(SchoolLocation $schoolLocation, CreateLicenseRequest $request)
    {
        $license = new License();

        $license->fill($request->all());

        if ($schoolLocation->licenses()->save($license) !== false) {
            return Response::make($license, 200);
        } else {
            return Response::make('Failed to create license', 500);
        }
    }

    /**
     * Display the specified license.
     * @param SchoolLocation $schoolLocation
     * @param License $license
     * @return
     */
    public function show(SchoolLocation $schoolLocation, License $license)
    {
        if ($license->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('License not found', 404);
        } else {
            return Response::make($license, 200);
        }
    }

    /**
     * Update the specified license in storage.
     * @param SchoolLocation $schoolLocation
     * @param License $license
     * @param UpdateLicenseRequest $request
     * @return
     */
    public function update(SchoolLocation $schoolLocation, License $license, UpdateLicenseRequest $request)
    {
        $license->fill($request->all());

        if ($schoolLocation->licenses()->save($license) !== false) {
            return Response::make($license, 200);
        } else {
            return Response::make('Failed to update license', 500);
        }
    }

    /**
     * Remove the specified license from storage.
     * @param SchoolLocation $schoolLocation
     * @param License $license
     * @throws \Exception
     * @return
     */
    public function destroy(SchoolLocation $schoolLocation, License $license)
    {
        if ($license->school_location_id !== $schoolLocation->getKey()) {
            return Response::make('License not found', 404);
        }

        if ($license->delete()) {
            return Response::make($license, 200);
        } else {
            return Response::make('Failed to delete license', 500);
        }
    }
}