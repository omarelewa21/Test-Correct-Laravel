<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\CreateTestTakeStatusRequest;
use tcCore\Http\Requests\UpdateTestTakeStatusRequest;
use tcCore\TestTakeStatus;

class TestTakeStatusesController extends Controller {

	/**
	 * Display a listing of the test take statuses.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$testTakeStatuses = TestTakeStatus::orderBy('name');

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				return Response::make($testTakeStatuses->get(), 200);
				break;
			case 'list':
				return Response::make($testTakeStatuses->pluck('name', 'id'), 200);
				break;
			case 'paginate':
			default:
				return Response::make($testTakeStatuses->paginate(15), 200);
				break;
		}
	}

	/**
	 * Store a newly created test take status in storage.
	 *
	 * @param CreateTestTakeStatusRequest $request
	 * @return Response

	public function store(CreateTestTakeStatusRequest $request)
	{
	//
	$testTakeStatuses = new TestTakeStatus($request->all());
	if ($testTakeStatuses->save()) {
	return Response::make($testTakeStatuses, 200);
	} else {
	return Response::make('Failed to create testTakeStatus', 500);
	}
	}*/

	/**
	 * Display the specified test take status.
	 *
	 * @param  TestTakeStatus  $testTakeStatus
	 * @return Response
	 */
	public function show(TestTakeStatus $testTakeStatus)
	{
		return Response::make($testTakeStatus, 200);
	}

	/**
	 * Update the specified test take status in storage.
	 *
	 * @param  TestTakeStatus $testTakeStatus
	 * @param UpdateTestTakeStatusRequest $request
	 * @return Response

	public function update(TestTakeStatus $testTakeStatus, UpdateTestTakeStatusRequest $request)
	{
	if ($testTakeStatus->update($request->all())) {
	return Response::make($testTakeStatus, 200);
	} else {
	return Response::make('Failed to update testTakeStatus', 500);
	}
	}*/

	/**
	 * Remove the specified test take status from storage.
	 *
	 * @param  TestTakeStatus  $testTakeStatus
	 * @return Response

	public function destroy(TestTakeStatus $testTakeStatus)
	{
	//
	if ($testTakeStatus->delete()) {
	return Response::make($testTakeStatus, 200);
	} else {
	return Response::make('Failed to delete test kind', 500);
	}
	}*/

}
