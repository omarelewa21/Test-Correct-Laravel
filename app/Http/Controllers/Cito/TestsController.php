<?php namespace tcCore\Http\Controllers\Cito;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Requests;
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
        try { // added for compatibility with mariadb
            $expression = DB::raw("set session optimizer_switch='condition_fanout_filter=off';");
            DB::statement($expression->getValue(DB::connection()->getQueryGrammar()));
        } catch (\Exception $e){}
		$tests = Test::citoFiltered($request->get('filter', []), $request->get('order', []))->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')->paginate(15);
		return Response::make($tests, 200);
	}

	/**
	 * Display the specified test.
	 *
	 * @param  Test  $test
	 * @return Response
	 */
	public function show(Test $test)
	{
		$test->load('educationLevel', 'author', 'author.school', 'author.schoolLocation', 'subject', 'period', 'testKind');
		return Response::make($test, 200);
	}

}
