<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreatePeriodRequest;
use tcCore\Http\Requests\UpdatePeriodRequest;
use tcCore\Period;

class PeriodsController extends Controller {


    /**
     * Display a listing of the periods.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $periods = Period::filtered($request->get('filter', []), $request->get('order', []));

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($periods->get(), 200);
                break;
            case 'list':
                return Response::make($periods->get(['id', 'name', 'start_date', 'end_date'])->keyBy('id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($periods->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created period in storage.
     *
     * @param CreatePeriodRequest $request
     * @return Response
     */
    public function store(CreatePeriodRequest $request)
    {
        $period = new Period();

        $period->fill($request->all());

        if ($period->save() !== false) {
            return Response::make($period, 200);
        } else {
            return Response::make('Failed to create period', 500);
        }
    }

    /**
     * Display the specified period.
     *
     * @param  Period  $period
     * @return Response
     */
    public function show(Period $period)
    {
        return Response::make($period, 200);
    }

    /**
     * Update the specified period in storage.
     *
     * @param  Period $period
     * @param UpdatePeriodRequest $request
     * @return Response
     */
    public function update(Period $period, UpdatePeriodRequest $request)
    {
        $period->fill($request->all());

        if ($period->save() !== false) {
            return Response::make($period, 200);
        } else {
            return Response::make('Failed to update period', 500);
        }
    }

    /**
     * Remove the specified period from storage.
     *
     * @param  Period  $period
     * @return Response
     */
    public function destroy(Period $period)
    {
        if ($period->delete()) {
            return Response::make($period, 200);
        } else {
            return Response::make('Failed to delete period', 500);
        }
    }

}
