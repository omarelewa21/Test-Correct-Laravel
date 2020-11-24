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
        $user->save();

        return $user->refresh()->allowedSchoolLocations->map(function($location) {
            return (object) [
                'id' => $location->id,
                'uuid' => $location->uuid,
                'name' => $location->name,
                'active' => $location->is(Auth::user()->schoolLocation),
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
        $user = User::whereUuid($request->get('user_uuid'))->first();

        $user->removeSchoolLocation(Auth::user()->schoolLocation);
    }

    public function getExistingTeachers(){


        if (!Auth::user()->hasRole('School manager')) {
            abort(403);
        }



        /**
         * select *, t2.user_id as active
        * from users
        * inner join user_roles on (users.id = user_roles.user_id and user_roles.`role_id` = 1)
        * left join (select user_id from school_location_user where school_location_id = 5) as t2
        * on (users.id = t2.user_id)
        ( where school_id = 2
         */

        // externalId toevoegen,

//        return User::selectRaw('users.*, t2.user_id as active')->join('user_roles', function ($join) {
//            $join->on('users.id', '=', 'user_roles.user_id')
//                ->where('user_roles.role_id', '=', 1); // teacher
//
//        })
//
//            ->leftJoin()
//            ->where('school_id', Auth::user()->school_id)->get();

        $users = User::join('user_roles', function ($join) {
            $join->on('users.id', '=', 'user_roles.user_id')
                ->where('user_roles.role_id', '=', 1); // teacher
        })->where('school_id', Auth::user()->school_id)->paginate(15);

        $withActiveUsers = $users->map(function($user)  {
            $user->active = $user->allowedSchoolLocations->contains(Auth::user()->schoolLocation);

            return $user;
        });

        return $withActiveUsers;
    }
}
