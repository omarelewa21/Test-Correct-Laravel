<?php namespace tcCore\Http\Controllers;

use tcCore\User;
use tcCore\School;
use tcCore\Section;
use tcCore\SchoolLocation;
use Illuminate\Http\Request;
use tcCore\SchoolLocationSection;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Enums\TestPackages;
use Illuminate\Support\Facades\Auth;
use tcCore\SchoolLocationSharedSection;
use Illuminate\Support\Facades\Response;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use tcCore\Http\Requests\CreateSchoolLocationRequest;
use tcCore\Http\Requests\UpdateSchoolLocationRequest;
use tcCore\Http\Requests\SchoolLocationAddDefaultSubjectsAndSectionsRequest;

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
                $locations = $schoolLocations->get(['name', 'id', 'license_type'])->mapWithKeys(function($location) use ($request) {
                    $name = $location->name;
                    if($request->has('with_trial_notation') && $location->hasTrialLicense()) {
                        $name = sprintf('%s (%s)', $name, __('school_location.TRIAL'));
                    }
                    return [$location->id => $name];
                });
                return Response::make($locations, 200);
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
        if(request()->has('withAvailableEditOptions')){
            $schoolLocation['lvs_options'] = $schoolLocation->getLvsOptions();
            $schoolLocation['sso_options'] = $schoolLocation->getSsoOptions();
            $schoolLocation['has_run_manual_import'] = $schoolLocation->hasRunManualImport();
            $schoolLocation['license_types'] = SchoolLocation::getAvailableLicenseTypes();
            $schoolLocation['test_packages'] = TestPackages::values();
        }
        $schoolLocation->append('feature_settings');
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
        if($request->has('school_id') && $request->school_id != $schoolLocation->school_id){
            $schoolLocation->sharedSections()->detach();
            $schoolLocation->schoolLocationSections->each(function(SchoolLocationSection $sharedSection){
                SchoolLocationSharedSection::where('section_id', $sharedSection->section_id)->delete();
            });
        }

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

    public function addDefaultSubjectsAndSections(SchoolLocationAddDefaultSubjectsAndSectionsRequest $request, SchoolLocation $schoolLocation)
    {
        try {
            $schoolLocation->addDefaultSectionsAndSubjects();
            return Response::make($schoolLocation);
        } catch(\Throwable $th){
            Bugsnag::notifyException($th);
            return Response::make($th->getMessage(),500);
        }
    }

    public function isAllowedNewPlayerAccess()
    {
        return Response::make(
            Auth::user()->schoolLocation->allow_new_player_access,
            200
        );
    }

    public function getAvailableSchoolLocationOptions()
    {
        $options = [
            'lvs'           => SchoolLocation::getLvsOptions(),
            'sso'           => SchoolLocation::getSsoOptions(),
            'license_types' => SchoolLocation::getAvailableLicenseTypes()
        ];
        return Response::make($options, 200);
    }
    public function getLvsType($schoolLocationId)
    {
        $lvsType = [SchoolLocation::whereUuid($schoolLocationId)->value('lvs_type')];
        return Response::make($lvsType, 200);
    }
}
