<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\FileManagement;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\User;

class FileManagementUsersController extends Controller
{
    public function index(Request $request)
    {
        $builder = FileManagement::getBuilderForUsers(Auth::user(), $request->get('type','testupload'));
        return response()->json($builder->toBase()->get());
    }
}
