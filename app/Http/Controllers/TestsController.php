<?php namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\DuplicateTestRequest;
use tcCore\Shortcode;
use tcCore\TemporaryLogin;
use tcCore\Test;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestRequest;
use tcCore\Http\Requests\UpdateTestRequest;
use tcCore\Lib\Question\QuestionGatherer;

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
            \DB::select(\DB::raw("set session optimizer_switch='condition_fanout_filter=off';"));
        } catch (\Exception $e){}

        $tests = Test::filtered($request->get('filter', []), $request->get('order', []))->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')->paginate(15);

        $tests->each(function ($test) {
            $test->append('has_duplicates');
        });
		return Response::make($tests, 200);
	}

    /**
     * Display a listing of the tests.
     *
     * @return Response
     */
    public function index2(Request $request)
    {
        // @@ see TC-160
        // we now alwas change the setting to make it faster and don't reverse it anymore
        // as on a new server we might forget to update this setting and it doesn't do any harm to do this extra query
        try { // added for compatibility with mariadb
            \DB::select(\DB::raw("set session optimizer_switch='condition_fanout_filter=off';"));
        } catch (\Exception $e){}
        $tests = Test::filtered2($request->get('filter', []), $request->get('order'))
            ->with('educationLevel', 'testKind', 'subject', 'author', 'author.school', 'author.schoolLocation')
            ->paginate(15);
//		\DB::select(\DB::raw("set session optimizer_switch='condition_fanout_filter=on';"));

        $tests->each(function ($test) {
            $test->append('has_duplicates');
        });
        return Response::make($tests, 200);
    }

	/**
	 * Store a newly created test in storage.
	 *
	 * @param CreateTestRequest $request
	 * @return Response
	 */
	public function store(CreateTestRequest $request)
	{
		//
		$test = new Test($request->all());
		$test->setAttribute('author_id', Auth::id());
		$test->setAttribute('owner_id', Auth::user()->school_location_id);
		if ($test->save()) {
			return Response::make($test, 200);
		} else {
			return Response::make('Failed to create', 500);
		}
	}

	/**
	 * Display the specified test.
	 *
	 * @param  Test  $test
	 * @return Response
	 */
	public function show(Test $test)
	{
		$test->load('educationLevel', 'author', 'author.school', 'author.schoolLocation', 'subject', 'period', 'testKind','owner');
		$test->append(    'has_duplicates');
		return Response::make($test, 200);
	}

	/**
	 * Update the specified test in storage.
	 *
	 * @param  Test $test
	 * @param UpdateTestRequest $request
	 * @return Response
	 */
	public function update(Test $test, UpdateTestRequest $request)
	{
		//
		$test->fill($request->all());
		if ($test->save() !== false) {
			return Response::make($test, 200);
		} else {
			return Response::make('Failed to update', 500);
		}
	}

	/**
	 * Remove the specified test from storage.
	 *
	 * @param  Test  $test
	 * @return Response
	 */
	public function destroy(Test $test)
	{
		//
		if ($test->delete()) {
			return Response::make($test, 200);
		} else {
			return Response::make('Failed to delete', 500);
		}

	}

	public function duplicate(Test $test, DuplicateTestRequest $request) {
		$test = $test->userDuplicate($request->all(), Auth::id());

		if ($test !== false) {
			return Response::make($test, 200);
		} else {
			return Response::make('Failed to duplicate tests', 500);
		}
	}

    public function maxScoreResponse(Test $test){
        return Response::make($test->maxScore(), 200);
    }

    public function withTemporaryLogin(Test $test)
    {
        $response = new \stdClass;
        $temporaryLogin = TemporaryLogin::createForUser(Auth()->user());

        $relativeUrl = sprintf('%s?redirect=%s',
            route('auth.temporary-login.redirect',[$temporaryLogin->uuid],false),
            rawurlencode(route('teacher.test-preview', $test->uuid,false))
        );
        if(Str::startsWith($relativeUrl,'/')) {
            $relativeUrl = Str::replaceFirst('/', '', $relativeUrl);
        }

        $response->url = sprintf('%s%s',config('app.base_url'),$relativeUrl);

        return  response()->json($response);
    }

    public function answerModelwithTemporaryLogin(Test $test)
    {
        $response = new \stdClass;
        $temporaryLogin = TemporaryLogin::createForUser(Auth()->user());

        $relativeUrl = sprintf('%s?redirect=%s',
            route('auth.temporary-login.redirect',[$temporaryLogin->uuid],false),
            rawurlencode(route('teacher.test-answer-model', $test->uuid,false))
        );
        if(Str::startsWith($relativeUrl,'/')) {
            $relativeUrl = Str::replaceFirst('/', '', $relativeUrl);
        }

        $response->url = sprintf('%s%s',config('app.base_url'),$relativeUrl);

        return  response()->json($response);
    }
}
