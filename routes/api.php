<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

Route::get('/', 'HomeController@index');

/* TEST MULTIPLE QUESTION STUFF */
Route::get('/testing/{id}', 'HomeController@test');

Route::post('auth', ['uses' => 'Auth\AuthController@getApiKey']);
Route::post('send_password_reset', ['uses' => 'Auth\PasswordController@sendPasswordReset']);
Route::post('password_reset', ['uses' => 'Auth\PasswordController@passwordReset']);

Route::group(['middleware' => ['api', 'dl', 'authorize', 'authorizeBinds', 'bindings']], function(){
	// Tests + children
	Route::post('test/{test}/duplicate', ['as' => 'test.duplicate', 'uses' => 'TestsController@duplicate']);
	Route::resource('test', 'TestsController', ['except' => ['create', 'edit']]);

	Route::put('test_question/{test_question}/reorder', 'TestQuestionsController@updateOrder');
	Route::resource('test_question', 'TestQuestionsController', ['except' => ['create', 'edit']]);
	Route::resource('test_question.attachment', 'TestQuestions\AttachmentsController', ['except' => ['create', 'edit']]);

//	Route::delete('test_question/{test_question}/completion_question_answer', ['as' => 'test_question.completion_question_answer.destroy_all', 'uses' => 'TestQuestions\CompletionQuestionAnswersController@destroyAll']);
//	Route::resource('test_question.completion_question_answer', 'TestQuestions\CompletionQuestionAnswersController', ['except' => ['create', 'edit']]);

	Route::delete('test_question/{test_question}/matching_question_answer', ['as' => 'test_question.matching_question_answer.destroy_all', 'uses' => 'TestQuestions\MatchingQuestionAnswersController@destroyAll']);
	Route::resource('test_question.matching_question_answer', 'TestQuestions\MatchingQuestionAnswersController', ['except' => ['create', 'edit']]);

	Route::delete('test_question/{test_question}/multiple_choice_question_answer', ['as' => 'test_question.multiple_choice_question_answer.destroy_all', 'uses' => 'TestQuestions\MultipleChoiceQuestionAnswersController@destroyAll']);
	Route::resource('test_question.multiple_choice_question_answer', 'TestQuestions\MultipleChoiceQuestionAnswersController', ['except' => ['create', 'edit']]);

	Route::delete('test_question/{test_question}/ranking_question_answer', ['as' => 'test_question.ranking_question_answer.destroy_all', 'uses' => 'TestQuestions\RankingQuestionAnswersController@destroyAll']);
	Route::resource('test_question.ranking_question_answer', 'TestQuestions\RankingQuestionAnswersController', ['except' => ['create', 'edit']]);

	Route::resource('group_question_question.attachment', 'GroupQuestionQuestions\AttachmentsController', ['except' => ['create', 'edit']]);

//	Route::delete('group_question_question/{group_question_question}/completion_question_answer', ['as' => 'group_question_question.completion_question_answer.destroy_all', 'uses' => 'GroupQuestionQuestions\CompletionQuestionAnswersController@destroyAll']);
	Route::resource('group_question_question.completion_question_answer', 'GroupQuestionQuestions\CompletionQuestionAnswersController', ['except' => ['create', 'edit']]);

	Route::delete('group_question_question/{group_question_question}/matching_question_answer', ['as' => 'group_question_question.matching_question_answer.destroy_all', 'uses' => 'GroupQuestionQuestions\MatchingQuestionAnswersController@destroyAll']);
	Route::resource('group_question_question.matching_question_answer', 'GroupQuestionQuestions\MatchingQuestionAnswersController', ['except' => ['create', 'edit']]);

	Route::delete('group_question_question/{group_question_question}/multiple_choice_question_answer', ['as' => 'group_question_question.multiple_choice_question_answer.destroy_all', 'uses' => 'GroupQuestionQuestions\MultipleChoiceQuestionAnswersController@destroyAll']);
	Route::resource('group_question_question.multiple_choice_question_answer', 'GroupQuestionQuestions\MultipleChoiceQuestionAnswersController', ['except' => ['create', 'edit']]);

	Route::delete('group_question_question/{group_question_question}/ranking_question_answer', ['as' => 'group_question_question.ranking_question_answer.destroy_all', 'uses' => 'GroupQuestionQuestions\RankingQuestionAnswersController@destroyAll']);
	Route::resource('group_question_question.ranking_question_answer', 'GroupQuestionQuestions\RankingQuestionAnswersController', ['except' => ['create', 'edit']]);

	Route::get('group_question_question/{group_question_question}', ['as' => 'group_question_question.index', 'uses' => 'GroupQuestionQuestionsController@index']);
	Route::post('group_question_question/{group_question_question}', ['as' => 'group_question_question.store', 'uses' => 'GroupQuestionQuestionsController@store']);
	Route::get('group_question_question/{group_question_question}/{group_question_question_id}', ['as' => 'group_question_question.index', 'uses' => 'GroupQuestionQuestionsController@show']);
	Route::put('group_question_question/{group_question_question}/{group_question_question_id}/reorder', ['as' => 'group_question_question.updateOrder', 'uses' => 'GroupQuestionQuestionsController@updateOrder']);
	Route::put('group_question_question/{group_question_question}/{group_question_question_id}', ['as' => 'group_question_question.update', 'uses' => 'GroupQuestionQuestionsController@update']);
	Route::patch('group_question_question/{group_question_question}/{group_question_question_id}', ['uses' => 'GroupQuestionQuestionsController@update']);
	Route::delete('group_question_question/{group_question_question}/{group_question_question_id}', ['as' => 'group_question_question.destroy', 'uses' => 'GroupQuestionQuestionsController@destroy']);

	//Route::resource('question', 'QuestionsController', ['except' => ['create', 'edit']]);
	//Route::resource('question.group_question', 'GroupQuestionQuestionsController', ['except' => ['create', 'edit']]);

	// Todo: Validation of attachments (including downloading non-existent files)
	Route::get('attachment/{attachment}/download', ['as' => 'attachment.download', 'uses' => 'AttachmentsController@download']);

	// Test take + children
	Route::get('test_take/{test_take}/export', ['as' => 'test_take.export', 'uses' => 'TestTakesController@export']);
	Route::post('test_take/{test_take}/normalize', ['as' => 'test_take.normalize', 'uses' => 'TestTakesController@normalize']);
	Route::post('test_take/{test_take}/next_question', ['as' => 'test_take.next_question', 'uses' => 'TestTakesController@nextQuestion']);
	Route::resource('test_take', 'TestTakesController', ['except' => ['create', 'edit']]);

	// Test take children
	Route::post('test_take/{test_take_id}/test_participant/{test_participant}/heartbeat', ['as' => 'test_take.test_participant.heartbeat', 'uses' => 'TestTakes\TestParticipantsController@heartbeat']);
	Route::resource('test_take.test_participant', 'TestTakes\TestParticipantsController', ['except' => ['create', 'edit']]);
	Route::resource('test_take.test_take_event', 'TestTakes\TestTakeEventsController', ['except' => ['create', 'edit']]);

	Route::resource('test_take_event_type', 'TestTakeEventTypesController', ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);

    Route::get('test_participant/{test_participant}/answers_status_and_questions2019',['uses' => 'TestParticipants\Answers2019Controller@getAnswersStatusAndQuestions']);
	Route::get('test_participant/{test_participant}/{test_take}/answers_status_and_test_take2019',['uses' => 'TestParticipants\Answers2019Controller@getAnswersStatusAndTestTake']);
    Route::get('test_participant/{test_participant}/answers_and_status2019',['uses' => 'TestParticipants\Answers2019Controller@getAnswersAndStatus']);

    // Test participant children
    Route::resource('test_participant.answer', 'TestParticipants\AnswersController', ['except' => ['create', 'edit']]);

    /**
     * Updated 2019
     */
	Route::put('test_participant/{test_participant}/answer2019/{answer}', ['uses' => 'TestParticipants\Answers2019Controller@update']);

	Route::get('test_participant/{test_participant}/question_and_answer2019/{question}', ['uses' => 'TestParticipants\Answers2019Controller@showQuestionAndAnswer']);

	// Education level
	Route::resource('education_level', 'EducationLevelsController', ['except' => ['create', 'edit']]);

	// School year + child
	Route::get('school_year/list', ['as' => 'school_year.list', 'uses' => 'SchoolYearsController@lists']);
	Route::resource('school_year', 'SchoolYearsController', ['except' => ['create', 'edit']]);

	Route::resource('period', 'PeriodsController', ['except' => ['create', 'edit']]);

	// Subjects
	Route::resource('subject', 'SubjectsController', ['except' => ['create', 'edit']]);

	// Test kinds
	Route::get('test_kind/list', ['as' => 'test_kind.list', 'uses' => 'TestKindsController@lists']);
	Route::resource('test_kind', 'TestKindsController', ['except' => ['create', 'edit']]);

	// Needed lookups
	Route::get('school_class/list', ['as' => 'school_class.list', 'uses' => 'SchoolClassesController@lists']);
	Route::resource('school_class', 'SchoolClassesController', ['except' => ['create', 'edit']]);

	Route::get('invigilator/list', ['as' => 'invigilator.list', 'uses' => 'InvigilatorsController@lists']);

	Route::get('student', ['as' => 'student.index', 'uses' => 'StudentsController@index']);

	// Shortcuts for questions show
	Route::get('question/{question}/bg', ['as' => 'question.bg', 'uses' => 'QuestionsController@bg']);
	Route::resource('question', 'QuestionsController', ['only' => ['index', 'show']]);
	Route::resource('attachment', 'AttachmentsController', ['only' => ['index', 'show']]);

	Route::resource('attainment', 'AttainmentsController', ['only' => ['index', 'show']]);

	// Phase B
	Route::resource('answer_rating', 'AnswerRatingsController', ['except' => ['create', 'edit']]);

	Route::resource('answer', 'AnswersController', ['only' => ['index', 'show']]);

	// Users
	Route::get('user/{user}/profile_image', ['as' => 'user.profile_image', 'uses' => 'UsersController@profileImage']);
	Route::get('user/send_welcome_email', ['as' => 'user.send_welcome_email', 'uses' => 'UsersController@sendWelcomeEmail']);
	Route::resource('user', 'UsersController', ['except' => ['create', 'edit']]);
	Route::resource('teacher', 'TeachersController', ['except' => ['create', 'edit']]);

	// Sales organization
	Route::resource('sales_organization', 'SalesOrganizationsController', ['except' => ['create', 'edit']]);

	// Umbrella organisation + child
	Route::resource('umbrella_organization', 'UmbrellaOrganizationsController', ['except' => ['create', 'edit']]);

	Route::resource('school', 'SchoolsController', ['except' => ['create', 'edit']]);
	// School children
	Route::resource('school_location', 'SchoolLocationsController', ['except' => ['create', 'edit']]);

	// School location children
	Route::resource('school_location.school_class', 'SchoolLocations\SchoolClassesController', ['except' => ['create', 'edit']]);
	Route::resource('school_location.school_location_ip', 'SchoolLocations\SchoolLocationIpsController', ['except' => ['create', 'edit']]);
	Route::resource('school_location.section', 'SchoolLocations\SectionsController', ['except' => ['create', 'edit']]);
	Route::resource('school_location.license', 'SchoolLocations\LicensesController', ['except' => ['create', 'edit']]);

	Route::resource('school_year', 'SchoolYearsController', ['except' => ['create', 'edit']]);
	Route::resource('school_year.period', 'SchoolYears\PeriodsController', ['except' => ['create', 'edit']]);

	Route::resource('section', 'SectionsController', ['except' => ['create', 'edit']]);
	Route::resource('subject', 'SubjectsController', ['except' => ['create', 'edit']]);

	Route::resource('message', 'MessageController', ['except' => ['create', 'edit']]);

	Route::resource('address', 'AddressesController', ['except' => ['create', 'edit']]);
	Route::resource('contact', 'ContactsController', ['except' => ['create', 'edit']]);
	Route::resource('grading_scale', 'GradingScalesController', ['except' => ['create', 'edit']]);

	Route::resource('base_subject', 'BaseSubjectsController', ['only' => ['index']]);

	Route::resource('tag', 'TagsController', ['only' => ['index', 'show']]);
});
