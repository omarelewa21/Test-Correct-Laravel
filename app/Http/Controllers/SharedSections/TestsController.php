<?php namespace tcCore\Http\Controllers\SharedSections;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\DuplicateTestRequest;
use tcCore\Test;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestRequest;
use tcCore\Http\Requests\UpdateTestRequest;

class TestsController extends Controller {

	/**
	 * Display a listing of the tests.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
        // @@ see TC-160
        // we now alwas change the setting to make it faster and don't reverse it anymore
        // as on a new server we might forget to update this setting and it doesn't do any harm to do this extra query
        \DB::select(\DB::raw("set session optimizer_switch='condition_fanout_filter=off';"));
		$tests = Test::sharedSectionsFiltered($request->get('filter', []), $request->get('order', []))->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')->paginate(15);
//		\DB::select(\DB::raw("set session optimizer_switch='condition_fanout_filter=on';"));
        $tests->each(function($test) {
            $test->append(    'has_duplicates');
        });
		return Response::make($tests, 200);
	}

//	public function duplicate(Test $test, DuplicateTestRequest $request) {
//		$test = $test->userDuplicate($request->all(), Auth::id());
//
//		if ($test !== false) {
//			return Response::make($test, 200);
//		} else {
//			return Response::make('Failed to duplicate tests', 500);
//		}
//	}

}
