<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\FileManagement;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\User;

class FileManagementSchoolLocationsController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(SchoolLocation::whereIn('id',(new SchoolHelper())->getRelatedSchoolLocationIds(Auth::user()))
            ->orderBy('name','asc')
            ->pluck('name','id'));
    }
}
