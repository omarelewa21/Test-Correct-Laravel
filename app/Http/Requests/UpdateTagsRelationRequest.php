<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;

class UpdateTagsRelationRequest extends Request {

	/**
	 * @var TagsRelation
	 */
	private $tagsRelation;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->tagsRelation = $route->parameter('tags_relation');
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$this->filterInput();

		return [
			//
		];
	}

	/**
	 * Get the sanitized input for the request.
	 *
	 * @return array
	 */
	public function sanitize()
	{
		return $this->all();
	}

}
