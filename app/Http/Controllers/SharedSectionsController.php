<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\DuplicateSharedSectionsTestRequest;
use tcCore\Http\Requests\DuplicateTestRequest;
use tcCore\Http\Requests\ShowSharedSectionsTestRequest;
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
	public function index(Requests\AllowOnlyAsSchoolManagerRequest $request)
	{
        $list = collect([]);
        Auth::user()->schoolLocation->sections->each(function(Section $section) use ($list){
            if($section->sharedSchoolLocations){
                $list->add($section);
            }
        });
		return Response::make($list, 200);
	}

//	public function show(ShowSharedSectionsTestRequest $request, Test $test)
//    {
//        return Response::make($test,200);
//    }
//
//
//	public function duplicate(Test $test, DuplicateSharedSectionsTestRequest $request) {
//		$test = $test->userDuplicate($request->all(), Auth::id());
//
//		if ($test !== false) {
//			return Response::make($test, 200);
//		} else {
//			return Response::make('Failed to duplicate tests', 500);
//		}
//	}

}
