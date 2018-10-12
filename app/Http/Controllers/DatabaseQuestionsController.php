<?php namespace tcCore\Http\Controllers;

use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\DatabaseQuestion;
use tcCore\Http\Requests\CreateDatabaseQuestionRequest;
use tcCore\Http\Requests\UpdateDatabaseQuestionRequest;

class DatabaseQuestionsController extends Controller {

	/**
	 * Display a listing of the database questions.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new database question.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created database question in storage.
	 *
	 * @param CreateDatabaseQuestionRequest $request
	 * @return Response
	 */
	public function store(CreateDatabaseQuestionRequest $request)
	{
		//
		$databaseQuestion = DatabaseQuestion::create($request->all());
	}

	/**
	 * Display the specified database question.
	 *
	 * @param  DatabaseQuestion  $databaseQuestion
	 * @return Response
	 */
	public function show(DatabaseQuestion $databaseQuestion)
	{
		//
	}

	/**
	 * Show the form for editing the specified database question.
	 *
	 * @param  DatabaseQuestion  $databaseQuestion
	 * @return Response
	 */
	public function edit(DatabaseQuestion $databaseQuestion)
	{
		//
	}

	/**
	 * Update the specified database question in storage.
	 *
	 * @param  DatabaseQuestion $databaseQuestion
	 * @param UpdateDatabaseQuestionRequest $request
	 * @return Response
	 */
	public function update(DatabaseQuestion $databaseQuestion, UpdateDatabaseQuestionRequest $request)
	{
		//
		$databaseQuestion->update($request->all());
	}

	/**
	 * Remove the specified database question from storage.
	 *
	 * @param  DatabaseQuestion  $databaseQuestion
	 * @return Response
	 */
	public function destroy(DatabaseQuestion $databaseQuestion)
	{
		//
		$databaseQuestion->delete();
	}

}
