<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\TestKind;
use tcCore\Http\Requests\CreateTestKindRequest;
use tcCore\Http\Requests\UpdateTestKindRequest;

class TestKindsController extends Controller {

    /**
     * Display a listing of the test kind.
     *
     * @return Response
     */
    public function index()
    {
        return Response::make(TestKind::paginate(15),200);
    }

    /**
     * Store a newly created test kind in storage.
     *
     * @param CreateTestKindRequest $request
     * @return Response
     */
    public function store(CreateTestKindRequest $request)
    {
        //
        $testKind = new TestKind($request->all());
        if ($testKind->save()) {
            return Response::make($testKind, 200);
        } else {
            return Response::make('Failed to create test kind', 500);
        }
    }

    /**
     * Display the specified test kind.
     *
     * @param  TestKind  $testKind
     * @return Response
     */
    public function show(TestKind $testKind)
    {
        return Response::make($testKind, 200);
    }

    /**
     * Update the specified test kind in storage.
     *
     * @param  TestKind $testKind
     * @param UpdateTestKindRequest $request
     * @return Response
     */
    public function update(TestKind $testKind, UpdateTestKindRequest $request)
    {
        //
        if ($testKind->update($request->all())) {
            return Response::make($testKind, 200);
        } else {
            return Response::make('Failed to update test kind', 500);
        }
    }

    /**
     * Remove the specified test kind from storage.
     *
     * @param  TestKind  $testKind
     * @return Response
     */
    public function destroy(TestKind $testKind)
    {
        //
        if ($testKind->delete()) {
            return Response::make($testKind, 200);
        } else {
            return Response::make('Failed to delete test kind', 500);
        }
    }

    /**
     * Returns an id and name-array for a select-box.
     *
     * @return Response
     */
    public function lists() {
        return Response::make(TestKind::orderBy('name', 'asc')->pluck('name', 'id'));
    }
}
