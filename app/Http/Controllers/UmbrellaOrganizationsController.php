<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\CreateUmbrellaOrganizationRequest;
use tcCore\Http\Requests\UpdateUmbrellaOrganizationRequest;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class UmbrellaOrganizationsController extends Controller {

	/**
	 * Display a listing of the umbrella organizations.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$umbrellaOrganizations = UmbrellaOrganization::filtered($request->get('filter', []), $request->get('order', []));

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				return Response::make($umbrellaOrganizations->get(), 200);
				break;
			case 'list':
				return Response::make($umbrellaOrganizations->select(['id', 'name', 'uuid'])->get()->keyBy('id'), 200);
				break;
			case 'paginate':
			default:
				return Response::make($umbrellaOrganizations->paginate(15), 200);
				break;
		}
	}

	/**
	 * Store a newly created umbrella organization in storage.
	 *
	 * @param CreateUmbrellaOrganizationRequest $request
	 * @return Response
	 */
	public function store(CreateUmbrellaOrganizationRequest $request)
	{
		$umbrellaOrganization = new UmbrellaOrganization();

		$umbrellaOrganization->fill($request->all());
		if (!$request->filled('user_id')) {
			$umbrellaOrganization->setAttribute('user_id', Auth::user()->getKey());
		}

		if ($umbrellaOrganization->save()) {
			return Response::make($umbrellaOrganization, 200);
		} else {
			return Response::make('Failed to create umbrella organization', 500);
		}
	}

	/**
	 * Display the specified umbrella organization.
	 *
	 * @param  UmbrellaOrganization  $umbrellaOrganization
	 * @return Response
	 */
	public function show(UmbrellaOrganization $umbrellaOrganization)
	{
		$umbrellaOrganization->load('user', 'umbrellaOrganizationAddresses', 'umbrellaOrganizationAddresses.address', 'umbrellaOrganizationContacts', 'umbrellaOrganizationContacts.contact', 'schools');
		return Response::make($umbrellaOrganization, 200);
	}

	/**
	 * Update the specified umbrella organization in storage.
	 *
	 * @param  UmbrellaOrganization $umbrellaOrganization
	 * @param UpdateUmbrellaOrganizationRequest $request
	 * @return Response
	 */
	public function update(UmbrellaOrganization $umbrellaOrganization, UpdateUmbrellaOrganizationRequest $request)
	{
		$umbrellaOrganization->fill($request->all());
		if ($umbrellaOrganization->save()) {
			return Response::make($umbrellaOrganization, 200);
		} else {
			return Response::make('Failed to update umbrella organization', 500);
		}
	}

	/**
	 * Remove the specified umbrella organization from storage.
	 *
	 * @param  UmbrellaOrganization  $umbrellaOrganization
	 * @return Response
	 */
	public function destroy(UmbrellaOrganization $umbrellaOrganization)
	{
		//
		if ($umbrellaOrganization->delete()) {
			return Response::make($umbrellaOrganization, 200);
		} else {
			return Response::make('Failed to delete umbrella organization', 500);
		}
	}

}
