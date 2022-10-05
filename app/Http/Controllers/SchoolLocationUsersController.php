<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\DuplicateTestRequest;
use tcCore\SchoolLocation;
use tcCore\Test;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestRequest;
use tcCore\Http\Requests\UpdateTestRequest;
use tcCore\User;

class SchoolLocationUsersController extends Controller {

    public function index()
    {
        return Auth::user()->allowedSchoolLocations->map(function($location) {
            return (object) [
                'id' => $location->id,
                'uuid' => $location->uuid,
                'name' => $location->name,
                'active' => $location->is(Auth::user()->schoolLocation),
                'language' => $location->school_language_cake,
            ];
        });
    }

    public function update(Request $request)
    {
        $schoolLocation = SchoolLocation::whereUuid($request->school_location)->first();
        if (! Auth::user()->isAllowedToSwitchToSchoolLocation($schoolLocation)) {
            abort(403);
        }

        $user = Auth::user()->schoolLocation()->associate($schoolLocation);
        $user->createTrialPeriodRecordIfRequired();
        $user->save();

        return $user->refresh()->allowedSchoolLocations->map(function($location) {
            return (object) [
                'id' => $location->id,
                'uuid' => $location->uuid,
                'name' => $location->name,
                'active' => $location->is(Auth::user()->schoolLocation),
                'language' => $location->school_language_cake
            ];
        });
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('School manager')) {
            abort(403);
        }

        $user = User::whereUuid($request->get('user_uuid'))->first();
//        $schoolLocation = SchoolLocation::whereUuid($request->get('school_location'))->first();

        $user->addSchoolLocation(Auth::user()->schoolLocation);
    }

    public function delete(Request $request) {
        if (!Auth::user()->hasRole('School manager')) {
            abort(403);
        }


        $user = User::whereUuid($request->get('user_uuid'))->first();

        $user->removeSchoolLocation(Auth::user()->schoolLocation);
        $user->removeSchoolLocationTeachers(Auth::user()->schoolLocation);
    }

    public function getExistingTeachers(Request $request){
        $filters = $request->get('filter',[]);


        if (!Auth::user()->hasRole('School manager')) {
            abort(403);
        }

        $schoolLocationIds = SchoolLocation::where('school_id',Auth::user()->schoolLocation->school_id)
            ->whereNotNull('school_id')->pluck('id');

        $qbUsers = User::join('user_roles', function ($join) {
            $join->on('users.id', '=', 'user_roles.user_id')
                ->where('user_roles.role_id', '=', 1); // teacher

        })->whereIn('school_location_id', $schoolLocationIds)
            ->where('demo', '<>', 1)
            ->where('school_location_id', '<>', Auth::user()->school_location_id)
            ->orderBy('name_first')
            ->orderBy('name');

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'username':
                    $qbUsers->where('username', 'LIKE', '%'.$value.'%');
                    break;
                case 'name_first':
                    $qbUsers->where('name_first', 'LIKE', '%'.$value.'%');
                    break;
                case 'name':
                    $qbUsers->where('name', 'LIKE', '%'.$value.'%');
                    break;
            }
        }


        $users = $qbUsers->paginate(15);

        return $users->map(function($user)  {
            $user->active = $user->allowedSchoolLocations->contains(Auth::user()->schoolLocation);
            $user->teacher_external_id  = $user->external_id;
            if($user->active){
                try {
                    $user->teacher_external_id = $user->allowedSchoolLocations()->where('school_location_id',Auth::user()->schoolLocation->id)->firstOrFail()->pivot->external_id;
                }catch(\Exception $e){
                    //silent fail
                }
            }
            return $user;
        });
    }
}
