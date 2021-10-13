<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\User;

class AuthorsController extends Controller
{
    public function index(Request $request)
    {
        $builder = $this->getBuilderForOwnSubjects($request);
        return response()->json($builder->toBase()->get());
    }

    private function getBuilderForOwnSubjects(Request $request)
    {
        return User::withTrashed()->whereIn('id', // find all users part of this selection
            Teacher::withTrashed()->whereIn('subject_id', // find the teachers with these subjects
                Subject::withTrashed()->whereIn('section_id', // get all subjects belonging to the section memberships
                    Auth::user()->sections()->select('sections.id')->where('demo',0) // get section memberships of this teacher where section is not part of the demo environment
                )->select('subjects.id')
            )->select('teachers.user_id')
        )->select('id','name_first','name_suffix','name')->groupBy('users.id');
    }


}
