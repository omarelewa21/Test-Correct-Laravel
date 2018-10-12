<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Tag;

class TagsController extends Controller {

	/**
	 * Display a listing of the tags.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$tags = Tag::filtered($request->get('filter', []), $request->get('order', []));
		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				return Response::make($tags->get(), 200);
				break;
			case 'list':
				return Response::make($tags->lists('name', 'id'), 200);
				break;
			case 'paginate':
			default:
				return Response::make($tags->paginate(15), 200);
				break;
		}
	}


	/**
	 * Display the specified tag.
	 *
	 * @param  Tag  $tag
	 * @return Response
	 */
	public function show(Tag $tag)
	{
		return Response::make($tag, 200);
	}
}
