<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\IndexRoleRequest;
use tcCore\Role;
use tcCore\Http\Requests\CreateRoleRequest;
use tcCore\Http\Requests\UpdateRoleRequest;

class RolesController extends Controller {

	/**
	 * Display a listing of the roles.
	 *
	 * @return Response
	 */
	public function index(IndexRoleRequest $request)
	{
        return Response::make(Role::all(), 200);
	}

	/**
	 * Show the form for creating a new role.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created role in storage.
	 *
	 * @param CreateRoleRequest $request
	 * @return Response
	 */
	public function store(CreateRoleRequest $request)
	{
		//
		$role = Role::create($request->all());
	}

	/**
	 * Display the specified role.
	 *
	 * @param  Role  $role
	 * @return Response
	 */
	public function show(Role $role)
	{
		//
	}

	/**
	 * Show the form for editing the specified role.
	 *
	 * @param  Role  $role
	 * @return Response
	 */
	public function edit(Role $role)
	{
		//
	}

	/**
	 * Update the specified role in storage.
	 *
	 * @param  Role $role
	 * @param UpdateRoleRequest $request
	 * @return Response
	 */
	public function update(Role $role, UpdateRoleRequest $request)
	{
		//
		$role->update($request->all());
	}

	/**
	 * Remove the specified role from storage.
	 *
	 * @param  Role  $role
	 * @return Response
	 */
	public function destroy(Role $role)
	{
		//
		$role->delete();
	}

}
