<?php

namespace tcCore\Http\Controllers\Exam;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Subject;
use tcCore\Http\Requests\CreateSubjectRequest;
use tcCore\Http\Requests\UpdateSubjectRequest;

class SubjectsController extends Controller {

//
	/**
	 * Display a listing of the subjects.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$subjects = Subject::examFiltered($request->get('filter', []), $request->get('order', ['name'=>'asc']))->with('baseSubject');

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				return Response::make($subjects->get(), 200);
				break;
			case 'list':
				return Response::make($subjects->pluck('name', 'id'), 200);
				break;
			case 'paginate':
			default:
				return Response::make($subjects->paginate(15), 200);
				break;
		}
	}

}
