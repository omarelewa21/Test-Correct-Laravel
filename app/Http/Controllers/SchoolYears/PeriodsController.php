<?php namespace tcCore\Http\Controllers\SchoolYears;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreatePeriodRequest;
use tcCore\Http\Requests\UpdatePeriodRequest;
use tcCore\Period;
use tcCore\SchoolYear;

class PeriodsController extends Controller {

	/**
	 * Display a listing of the periods.
	 *
	 * @return Response
	 */
	public function index(SchoolYear $schoolYear, Request $request)
	{
		$periods = $schoolYear->period()->filtered($request->get('filter', []), $request->get('order', []))->with('baseSubject');

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				return Response::make($periods->get(), 200);
				break;
			case 'list':
				return Response::make($periods->pluck('name', 'id'), 200);
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
	public function store(SchoolYear $schoolYear, CreatePeriodRequest $request)
	{
		$period = new Period();

		$period->fill($request->all());

		if ($schoolYear->periods()->save($period) !== false) {
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
	public function show(SchoolYear $schoolYear, Period $period)
	{
		if ($period->school_year_id !== $schoolYear->getKey()) {
			return Response::make('Period not found', 404);
		} else {
			return Response::make($period, 200);
		}
	}

	/**
	 * Update the specified period in storage.
	 *
	 * @param  Period $period
	 * @param UpdatePeriodRequest $request
	 * @return Response
	 */
	public function update(SchoolYear $schoolYear, Period $period, UpdatePeriodRequest $request)
	{
		$period->fill($request->all());

		if ($schoolYear->periods()->save($period) !== false) {
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
	public function destroy(SchoolYear $schoolYear, Period $period)
	{
		if ($period->school_year_id !== $schoolYear->getKey()) {
			return Response::make('Period not found', 404);
		}

		if ($period->delete()) {
			return Response::make($period, 200);
		} else {
			return Response::make('Failed to delete period', 500);
		}
	}

	/**
	 * Returns an id and name-array for a select-box.
	 *
	 * @return Response
	 */
	public function lists(SchoolYear $schoolYear) {
		return Response::make($schoolYear->periods()->orderBy('name', 'asc')->pluck('name', 'id'));
	}

}
