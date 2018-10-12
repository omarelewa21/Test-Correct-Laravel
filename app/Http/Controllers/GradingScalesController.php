<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\GradingScale;
use tcCore\Http\Requests\CreateGradingScaleRequest;
use tcCore\Http\Requests\UpdateGradingScaleRequest;

class GradingScalesController extends Controller {

	/**
	 * Display a listing of the grading scales.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$gradingScales = GradingScale::filtered($request->get('filter', []), $request->get('order', []));

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				return Response::make($gradingScales->get(), 200);
				break;
			case 'list':
				return Response::make($gradingScales->lists('name', 'id'), 200);
				break;
			case 'paginate':
			default:
				return Response::make($gradingScales->paginate(15), 200);
				break;
		}
	}

	/**
	 * Store a newly created grading scale in storage.
	 *
	 * @param CreateGradingScaleRequest $request
	 * @return Response
	 */
	public function store(CreateGradingScaleRequest $request)
	{
		$gradingScale = new GradingScale($request->all());
		if ($gradingScale->save()) {
			return Response::make($gradingScale, 200);
		} else {
			return Response::make('Failed to create grading scale', 500);
		}
	}

	/**
	 * Display the specified grading scale.
	 *
	 * @param  GradingScale  $gradingScale
	 * @return Response
	 */
	public function show(GradingScale $gradingScale)
	{
		return Response::make($gradingScale, 200);
	}

	/**
	 * Update the specified grading scale in storage.
	 *
	 * @param  GradingScale $gradingScale
	 * @param UpdateGradingScaleRequest $request
	 * @return Response
	 */
	public function update(GradingScale $gradingScale, UpdateGradingScaleRequest $request)
	{
		//
		if ($gradingScale->update($request->all())) {
			return Response::make($gradingScale, 200);
		} else {
			return Response::make('Failed to update grading scale', 500);
		}
	}

	/**
	 * Remove the specified grading scale from storage.
	 *
	 * @param  GradingScale  $gradingScale
	 * @return Response
	 */
	public function destroy(GradingScale $gradingScale)
	{
		//
		if ($gradingScale->delete()) {
			return Response::make($gradingScale, 200);
		} else {
			return Response::make('Failed to delete grading scale', 500);
		}
	}

}
