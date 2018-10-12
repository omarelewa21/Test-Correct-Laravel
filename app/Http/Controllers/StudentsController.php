<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Controllers\Controller;
use tcCore\User;

class StudentsController extends Controller {

	/**
	 * Display a listing of the tests.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$students = User::studentFiltered($request->get('filter', []), $request->get('order', []));

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				return Response::make($students->get(['users.*']), 200);
				break;
			case 'list':
				return Response::make($students->get(['users.id', 'users.name_first', 'users.name_suffix', 'users.name'])->keyBy('id'), 200);
				break;
			case 'paginate':
			default:
				return Response::make($students->paginate(15, ['users.*']), 200);
				break;
		}
	}


}
