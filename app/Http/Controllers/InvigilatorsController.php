<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Role;
use tcCore\SchoolLocation;
use tcCore\User;
use tcCore\UserRole;

class InvigilatorsController extends Controller
{

    /**
     * Returns an id and name-array for a select-box.
     *
     * @return Response
     */
    public function lists()
    {
        $result = self::getInvigilatorList();
        if ($result === false) {
            return Response::make('User not attached to school or school location', 403);
        }

        return Response::make($result);
    }

    public static function getInvigilatorList()
    {
        $fields = ['id', 'name_first', 'name_suffix', 'name'];

        $query = User::withTrashed()->orderBy('name', 'asc')
            ->join(with(new UserRole())->getTable(), 'user_roles.user_id', '=', 'users.id')
            ->whereIn('user_roles.role_id', function ($query) {
                $query->select('id')
                    ->from(with(new Role())->getTable())
                    ->whereIn('name', ['Teacher', 'Invigilator'])
                    ->where('deleted_at', null);
            })->where('users.deleted_at', null)
            ->where('user_roles.deleted_at', null);

        $user = Auth::user()->getAttributes();

        if (array_key_exists('school_id',
                $user) && $user['school_id'] !== null && array_key_exists('school_location_id',
                $user) && $user['school_location_id'] !== null) {
            $query->where(function ($query) use ($user) {
                $query->where('school_id', $user['school_id'])
                    ->orWhere('school_location_id', $user['school_location_id']);
            });
        } elseif (array_key_exists('school_id', $user) && $user['school_id'] !== null) {
            $query->where('school_id', $user['school_id']);
        } elseif (array_key_exists('school_location_id', $user) && $user['school_location_id'] !== null) {
            $query->where('school_location_id', $user['school_location_id']);
        } else {
            return false;
        }

        $query->union(User::select($fields)->join(with(new UserRole())->getTable(), 'user_roles.user_id', '=',
            'users.id')
            ->whereIn('users.id',
                DB::table('school_location_user')->select('user_id')->where('school_location_id', $user['school_location_id']))
        );

        return $query->get($fields)->keyBy('id');
    }

}
