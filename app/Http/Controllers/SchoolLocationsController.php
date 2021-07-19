<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\CreateSchoolLocationRequest;
use tcCore\Http\Requests\UpdateSchoolLocationRequest;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

class SchoolLocationsController extends Controller {
    /**
     * Display a listing of the school locations.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $schoolLocations = SchoolLocation::filtered($request->get('filter', []), $request->get('order', []))->with('school');
        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($schoolLocations->get(), 200);
                break;
            case 'list':
                return Response::make($schoolLocations->pluck('name', 'id'), 200);
                break;
            //list-uuid instead of replacing list for backwards compatibility
            case 'list-uuid':
                return Response::make($schoolLocations->select(['id', 'name', 'uuid'])->get()->keyBy('uuid'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($schoolLocations->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created school location in storage.
     *
     * @param CreateSchoolLocationRequest $request
     * @return Response
     */
    public function store(CreateSchoolLocationRequest $request)
    {
        $schoolLocation = new SchoolLocation();

        $data = $request->all();

        if (!isset($data['user_id'])) {
            $data['user_id'] = Auth::user()->getKey();
        }

        $schoolLocation->fill($data);

        if ($schoolLocation->save() !== false) {
            return Response::make($schoolLocation, 200);
        } else {
            return Response::make('Failed to create school location', 500);
        }
    }

    /**
     * Display the specified school location.
     *
     * @param  SchoolLocation  $schoolLocation
     * @return Response
     */
    public function show(SchoolLocation $schoolLocation)
    {
        $schoolLocation->load('user', 'school', 'schoolLocationAddresses', 'schoolLocationAddresses.address', 'schoolLocationContacts', 'schoolLocationContacts.contact', 'licenses', 'educationLevels');
        if(request()->has('withLvsAndSso')){
            $schoolLocation['lvs_options'] = $schoolLocation->getLvsOptions();
            $schoolLocation['sso_options'] = $schoolLocation->getSsoOptions();
            $schoolLocation['has_run_manual_import'] = $schoolLocation->hasRunManualImport();
        }
        return Response::make($schoolLocation, 200);
    }

    /**
     * Update the specified school location in storage.
     *
     * @param SchoolLocation $schoolLocation
     * @param UpdateSchoolLocationRequest $request
     * @return Response
     */
    public function update(SchoolLocation $schoolLocation, UpdateSchoolLocationRequest $request)
    {
        $schoolLocation->fill($request->all());

        if ($schoolLocation->save() !== false) {
            return Response::make($schoolLocation, 200);
        } else {
            return Response::make('Failed to update school location', 500);
        }
    }

    /**
     * Remove the specified school location from storage.
     *
     * @param  School  $attachment
     * @return Response
     */
    public function destroy(SchoolLocation $schoolLocation)
    {
        if ($schoolLocation->delete()) {
            return Response::make($schoolLocation, 200);
        } else {
            return Response::make('Failed to delete school location', 500);
        }
    }

    public function isAllowedNewPlayerAccess()
    {
        return Response::make(
            Auth::user()->schoolLocation->allow_new_player_access,
            200
        );
    }

    public function getLvsAndSsoOptions()
    {
        $lvs_options = ['lvs' => SchoolLocation::getLvsOptions()];
        $sso_options = ['sso' => SchoolLocation::getSsoOptions()];

        return Response::make(
            $lvs_options+$sso_options,
            200
        );
    }
    public function getLvsType($schoolLocationId)
    {
        $lvsType = [SchoolLocation::whereUuid($schoolLocationId)->value('lvs_type')];
        return Response::make($lvsType, 200);
    }
}