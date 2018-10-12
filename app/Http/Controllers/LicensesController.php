<?php namespace tcCore\Http\Controllers;

use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\License;
use tcCore\Http\Requests\CreateLicenseRequest;
use tcCore\Http\Requests\UpdateLicenseRequest;

class LicensesController extends Controller {

	/**
	 * Display a listing of the licenses.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new license.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created license in storage.
	 *
	 * @param CreateLicenseRequest $request
	 * @return Response
	 */
	public function store(CreateLicenseRequest $request)
	{
		//
		$license = License::create($request->all());
	}

	/**
	 * Display the specified license.
	 *
	 * @param  License  $license
	 * @return Response
	 */
	public function show(License $license)
	{
		//
	}

	/**
	 * Show the form for editing the specified license.
	 *
	 * @param  License  $license
	 * @return Response
	 */
	public function edit(License $license)
	{
		//
	}

	/**
	 * Update the specified license in storage.
	 *
	 * @param  License $license
	 * @param UpdateLicenseRequest $request
	 * @return Response
	 */
	public function update(License $license, UpdateLicenseRequest $request)
	{
		//
		$license->update($request->all());
	}

	/**
	 * Remove the specified license from storage.
	 *
	 * @param  License  $license
	 * @return Response
	 */
	public function destroy(License $license)
	{
		//
		$license->delete();
	}

}
