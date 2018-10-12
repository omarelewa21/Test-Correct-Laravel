<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Teacher;
use tcCore\Http\Requests\CreateTeacherRequest;
use tcCore\Http\Requests\UpdateTeacherRequest;

class TeachersController extends Controller {

	/**
	 * Display a listing of the teachers.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$teachers = Teacher::filtered($request->get('filter', []), $request->get('order', []));

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				return Response::make($teachers->get(['teachers.*']), 200);
				break;
			case 'paginate':
			default:
				return Response::make($teachers->paginate(15, ['teachers.*']), 200);
				break;
		}
	}

	/**
	 * Store a newly created teacher in storage.
	 *
	 * @param CreateTeacherRequest $request
	 * @return Response
	 */
	public function store(CreateTeacherRequest $request)
	{
        /**
         * @var Teacher $teacher
         */
		$teacher = Teacher::withTrashed()
            ->firstOrNew($request->only(['user_id', 'class_id', 'subject_id']));
		$teacher->fill($request->all());
        if ($teacher->trashed()) {
            if ($teacher->restore()) {
                return Response::make($teacher, 200);
            } else {
                return Response::make('Failed to restore teacher', 500);
            }
        } elseif ($teacher->save()) {
			return Response::make($teacher, 200);
		} else {
			return Response::make('Failed to create teacher', 500);
		}
	}

	/**
	 * Display the specified school class.
	 * @param  Teacher $teacher
	 * @return
	 */
	public function show(Teacher $teacher)
	{
		$teacher->load('Subject', 'User');

		return Response::make($teacher, 200);
	}

	/**
	 * Update the specified teacher in storage.
	 *
	 * @param  Teacher $teacher
	 * @param UpdateTeacherRequest $request
	 * @return Response
	 */
	public function update(Teacher $teacher, UpdateTeacherRequest $request)
	{
		//
		if ($teacher->update($request->all())) {
			return Response::make($teacher, 200);
		} else {
			return Response::make('Failed to update teacher', 500);
		}
	}

	/**
	 * Remove the specified teacher from storage.
	 *
	 * @param  Teacher  $teacher
	 * @return Response
	 */
	public function destroy(Teacher $teacher)
	{
		//
		if ($teacher->delete()) {
			return Response::make($teacher, 200);
		} else {
			return Response::make('Failed to delete teacher', 500);
		}
	}

}
