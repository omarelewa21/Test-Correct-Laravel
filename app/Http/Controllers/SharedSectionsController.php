<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\DuplicateSharedSectionsTestRequest;
use tcCore\Http\Requests\DuplicateTestRequest;
use tcCore\Http\Requests\IndexSharedSectionsRequest;
use tcCore\Http\Requests\OptionalSharedSectionSchoolLocationsRequest;
use tcCore\Http\Requests\ShowSharedSectionsTestRequest;
use tcCore\Http\Requests\StoreSharedSectionSchoolLocationRequest;
use tcCore\SchoolLocation;
use tcCore\Section;
use tcCore\Test;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestRequest;
use tcCore\Http\Requests\UpdateTestRequest;

class SharedSectionsController extends Controller {

	/**
	 * Display a listing of the tests.
	 *
	 * @return Response
	 */
	public function index(IndexSharedSectionsRequest $request, Section $section)
	{
		return Response::make($section->sharedSchoolLocations, 200);
	}

	public function optionalSchoolLocations(OptionalSharedSectionSchoolLocationsRequest $request, Section $section)
    {
        $schoolLocations = SchoolLocation::where('school_id',Auth::user()->schoolLocation->school_id)
            ->whereNotNull('school_id')
            ->whereNotIn('id',$section->sharedSchoolLocations()->select('id'))
            ->where('id','!=',Auth::user()->schoolLocation->getKey())
            ->get();
        $return = collect([]);
        $schoolLocations->each(function(SchoolLocation $sl) use ($return){
            $return[$sl->uuid] = $sl->name;
        });
        return Response::make($return, 200);
    }

	public function store(StoreSharedSectionSchoolLocationRequest $request, Section $section){
        $schoolLocationId = $request->input('school_location_id');
        $section->sharedSchoolLocations()->attach($schoolLocationId);
        return Response::make($section, 200);
    }

    public function destroy(Request $request, Section $section, SchoolLocation $school_location)
    {
        $school_location->sharedSections()->detach($section->getKey());
        return Response::make($section, 200);
    }

}
