<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\EducationLevel;
use tcCore\Http\Requests\CreateEducationLevelRequest;
use tcCore\Http\Requests\UpdateEducationLevelRequest;

class EducationLevelsController extends Controller {

	/**
	 * Display a listing of the education levels.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$educationLevels = EducationLevel::filtered($request->get('filter', []), $request->get('order', []));

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				return Response::make($educationLevels->get(), 200);
				break;
			case 'list':
				return Response::make($educationLevels->select(['id', 'name', 'max_years'])->get()->keyBy('id'), 200);
				break;
			case 'paginate':
			default:
				return Response::make($educationLevels->paginate(15), 200);
				break;
		}
	}

	/**
	 * Store a newly created education level in storage.
	 *
	 * @param CreateEducationLevelRequest $request
	 * @return Response
	 */
	public function store(CreateEducationLevelRequest $request)
	{
		//
		$educationLevel = new EducationLevel($request->all());
		if ($educationLevel->save()) {
			return Response::make($educationLevel, 200);
		} else {
			return Response::make('Failed to create education level', 500);
		}
	}

	/**
	 * Display the specified education level.
	 *
	 * @param  EducationLevel  $educationLevel
	 * @return Response
	 */
	public function show(EducationLevel $educationLevel)
	{
		return Response::make($educationLevel, 200);
	}

	/**
	 * Update the specified education level in storage.
	 *
	 * @param  EducationLevel $educationLevel
	 * @param UpdateEducationLevelRequest $request
	 * @return Response
	 */
	public function update(EducationLevel $educationLevel, UpdateEducationLevelRequest $request)
	{
		//
		if ($educationLevel->update($request->all())) {
			return Response::make($educationLevel, 200);
		} else {
			return Response::make('Failed to update education level', 500);
		}
	}

	/**
	 * Remove the specified education level from storage.
	 *
	 * @param  EducationLevel  $educationLevel
	 * @return Response
	 */
	public function destroy(EducationLevel $educationLevel)
	{
		if ($educationLevel->delete()) {
			return Response::make($educationLevel, 200);
		} else {
			return Response::make('Failed to delete education level', 500);
		}
	}
}