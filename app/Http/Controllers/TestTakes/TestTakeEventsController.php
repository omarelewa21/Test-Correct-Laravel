<?php namespace tcCore\Http\Controllers\TestTakes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Events\RemoveFraudDetectionNotification;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\TestTakeEvent;
use tcCore\Http\Requests\CreateTestTakeEventRequest;
use tcCore\Http\Requests\UpdateTestTakeEventRequest;
use tcCore\TestTake;

class TestTakeEventsController extends Controller {

    /**
     * Display a listing of the test take events.
     *
     * @return Response
     */
    public function index(TestTake $testTake, Request $request)
    {
        $testTakeEvents = $testTake->testTakeEvents()->with('testTakeEventType', 'testTake', 'testParticipant', 'testParticipant.user');
        (new TestTakeEvent())->scopeFiltered($testTakeEvents, $request->get('filter', []), $request->get('order', []));

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($testTakeEvents->get(), 200);
                break;
            case 'list':
                return Response::make($testTakeEvents->get()->keyBy('id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($testTakeEvents->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created test take event in storage.
     *
     * @param CreateTestTakeEventRequest $request
     * @return Response
     */
    public function store(TestTake $testTake, CreateTestTakeEventRequest $request)
    {
        $testTakeEvent = new TestTakeEvent();

        $testTakeEvent->fill($request->all());

        if ($testTake->testTakeEvents()->save($testTakeEvent) !== false) {
            return Response::make($testTakeEvent, 200);
        } else {
            return Response::make('Failed to create test take event', 500);
        }
    }

    /**
     * Display the specified test take event.
     *
     * @param  TestTakeEvent  $testTakeEvent
     * @return Response
     */
    public function show(TestTake $testTake, TestTakeEvent $testTakeEvent)
    {
        if ($testTakeEvent->test_take_id !== $testTake->getKey()) {
            return Response::make('Test take event not found', 404);
        } else {
            return Response::make($testTakeEvent, 200);
        }
    }

    /**
     * Update the specified test take event in storage.
     *
     * @param  TestTakeEvent $testTakeEvent
     * @param UpdateTestTakeEventRequest $request
     * @return Response
     */
    public function update(TestTake $testTake, TestTakeEvent $testTakeEvent, UpdateTestTakeEventRequest $request)
    {
        $testTakeEvent->fill($request->all());

        if ($testTake->testTakeEvents()->save($testTakeEvent) !== false) {
            return Response::make($testTakeEvent, 200);
        } else {
            return Response::make('Failed to update test take event', 500);
        }
    }

    /**
     * Remove the specified test take event from storage.
     *
     * @param  TestTakeEvent  $testTakeEvent
     * @return Response
     */
    public function destroy(TestTake $testTake, TestTakeEvent $testTakeEvent)
    {
        if ($testTakeEvent->test_take_id !== $testTake->getKey()) {
            return Response::make('TestTakePublicEvent not found', 404);
        }

        if ($testTakeEvent->delete()) {
            return Response::make($testTakeEvent, 200);
        } else {
            return Response::make('Failed to delete test take event', 500);
        }
    }

}
