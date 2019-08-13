<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\TestTakeEventType;
/*use tcCore\Http\Requests\CreateTestTakeEventTypeRequest;
use tcCore\Http\Requests\UpdateTestTakeEventTypeRequest;*/

class TestTakeEventTypesController extends Controller {

    /**
     * Display a listing of the test take event types.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $testTakeEventTypes = TestTakeEventType::orderBy('name');

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($testTakeEventTypes->get(), 200);
                break;
            case 'list':
                return Response::make($testTakeEventTypes->pluck('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($testTakeEventTypes->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created test take event type in storage.
     *
     * @param CreateTestTakeEventTypeRequest $request
     * @return Response

    public function store(CreateTestTakeEventTypeRequest $request)
    {
        //
        $testTakeEventTypes = new TestTakeEventType($request->all());
        if ($testTakeEventTypes->save()) {
            return Response::make($testTakeEventTypes, 200);
        } else {
            return Response::make('Failed to create testTakeEventType', 500);
        }
    }*/

    /**
     * Display the specified test take event type.
     *
     * @param  TestTakeEventType  $testTakeEventType
     * @return Response
     */
    public function show(TestTakeEventType $testTakeEventType)
    {
        return Response::make($testTakeEventType, 200);
    }

    /**
     * Update the specified test take event type in storage.
     *
     * @param  TestTakeEventType $testTakeEventType
     * @param UpdateTestTakeEventTypeRequest $request
     * @return Response

    public function update(TestTakeEventType $testTakeEventType, UpdateTestTakeEventTypeRequest $request)
    {
        if ($testTakeEventType->update($request->all())) {
            return Response::make($testTakeEventType, 200);
        } else {
            return Response::make('Failed to update testTakeEventType', 500);
        }
    }*/

    /**
     * Remove the specified test take event type from storage.
     *
     * @param  TestTakeEventType  $testTakeEventType
     * @return Response

    public function destroy(TestTakeEventType $testTakeEventType)
    {
        //
        if ($testTakeEventType->delete()) {
            return Response::make($testTakeEventType, 200);
        } else {
            return Response::make('Failed to delete test kind', 500);
        }
    }*/

}
