<?php namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\IndexAdminTeacherStatsRequest;
use tcCore\Teacher;
use tcCore\TestTake;

class AdminTeacherStatsController extends Controller {

	/**
	 * Display a listing of the users.
	 *
	 * @return Response
	 */
	public function index(IndexAdminTeacherStatsRequest $request)
	{

	    $teachers = Teacher::with('user')->get();
	    $teacherUsers = Teacher::with('user')->get()->map(function($t) {
            return $t->user;
        });


	    // nonTeachers => nog geen toets gemaakt
        $nonTeacherUsers = $teacherUsers->filter(function($t){
            return null == $t->tests || $t->tests->count() == 0;
        });

        //smallTeachers => laatste 6 maanden toets afgenomen en laatste 2 maanden niet
        $authors = TestTake::where('time_start','>=',Carbon::now()->subMonth(6))->where('time_start','<=',Carbon::now()->subMonth(2))->get()->map(function($testTake){
            return $testTake->user;
        })->unique();

        $lastMonthAuthors = TestTake::where('time_start','>=',Carbon::now()->subMonth(2))->get()->map(function($testTake){
            return $testTake->user_id;
        })->unique();

        $smallUserIds = $authors->whereNotIn('id',$lastMonthAuthors->toArray());

        $smallTeacherUsers = $teacherUsers->whereIn('id',$smallUserIds->toArray());

        // mediumTeachers => afgelopen 2 maanden 1 toets afgenomen

        $mediumTeacherUsers = $teacherUsers->filter(function($u){
            return $u->testTakes->where('time_start','>=',Carbon::now()->subMonth(1))->count() == 1;
        });

        // heavyTeachers => afgelopen maand een toets afgenomen
        $heavyTeacherUsers = $teacherUsers->filter(function($u){
            return $u->testTakes->where('time_start','>=',Carbon::now()->subMonth(1))->count() > 0;
        });

        return Response::make([
            'nonUsers' => $nonTeacherUsers->map(function($u){
                   return ['email' => $u->username,'school' => $u->schoolLocation->name,'totalTestTakes' => 0];
                })->toArray(),
            'smallUsers' => $smallTeacherUsers->map(function($u){
                    return [
                        'email' => $u->username,
                        'school' => $u->schoolLocation->name,
                        'totalTestTakes' => $u->testTakes->count(),
                    ];
                })->toArray(),
            'mediumUsers' => $mediumTeacherUsers->map(function($u){
                return [
                    'email' => $u->username,
                    'school' => $u->schoolLocation->name,
                    'totalTestTakes' => $u->testTakes->count(),
                ];
            })->toArray(),
            'heavyUsers' => $heavyTeacherUsers->map(function($u){
                return [
                    'email' => $u->username,
                    'school' => $u->schoolLocation->name,
                    'totalTestTakes' => $u->testTakes->count(),
                ];
            })->toArray(),
        ],200);
	}

}
