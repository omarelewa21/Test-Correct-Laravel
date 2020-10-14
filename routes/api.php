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

Route::get('/edu-k', 'EduK\HomeController@index');
Route::post('demo_account', 'DemoAccountController@store')->name('demo_account.store');

Route::get('/', 'HomeController@index');

/* TEST MULTIPLE QUESTION STUFF */
Route::get('/testing/{id}', 'HomeController@test');

Route::post('auth', ['uses' => 'Auth\AuthController@getApiKey']);
Route::post('send_password_reset', ['uses' => 'Auth\PasswordController@sendPasswordReset']);
Route::post('password_reset', ['uses' => 'Auth\PasswordController@passwordReset']);

Route::get('edu-ix/{ean}/{session_id}/{signature}', 'EduK\HomeController@create');
Route::post('edu-ix/{ean}/{session_id}/{edu_ix_signature}', 'EduK\HomeController@store');

Route::group(['middleware' => ['api', 'dl', 'authorize', 'authorizeBinds', 'bindings']], function(){

    // app_version_info
    Route::post('/app_version_info',['as' => 'app_version_info.store','uses' => 'AppVersionInfoController@store']);
	// Onboarding
    Route::post('/onboarding/registeruserstep',['as' => 'onboarding.register_userstep','uses' => 'OnboardingWizardController@registerUserStep']);
    Route::get('/onboarding/{user}/steps',['as' => 'onboarding.show_steps_for_user','uses' => 'OnboardingWizardController@showStepsForUser']);
    Route::put('/onboarding',['as' => 'onboarding.update','uses' => 'OnboardingWizardController@update']);

    // Tests + children
	Route::post('test/{test}/duplicate', ['as' => 'test.duplicate', 'uses' => 'TestsController@duplicate']);
	Route::resource('test', 'TestsController', ['except' => ['create', 'edit']]);

    Route::resource('cito_test','Cito\TestsController')->only(['index','show']);

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
	Route::get('group_question_question/{group_question_question}/{group_question_question_id}', ['as' => 'group_question_question.show', 'uses' => 'GroupQuestionQuestionsController@show']);
	Route::put('group_question_question/{group_question_question}/{group_question_question_id}/reorder', ['as' => 'group_question_question.updateOrder', 'uses' => 'GroupQuestionQuestionsController@updateOrder']);
	Route::put('group_question_question/{group_question_question}/{group_question_question_id}', ['as' => 'group_question_question.update', 'uses' => 'GroupQuestionQuestionsController@update']);
	Route::patch('group_question_question/{group_question_question}/{group_question_question_id}', ['uses' => 'GroupQuestionQuestionsController@update']);
	Route::delete('group_question_question/{group_question_question}/{group_question_question_id}', ['as' => 'group_question_question.destroy', 'uses' => 'GroupQuestionQuestionsController@destroy']);

	//Route::resource('question', 'QuestionsController', ['except' => ['create', 'edit']]);
	//Route::resource('question.group_question', 'GroupQuestionQuestionsController', ['except' => ['create', 'edit']]);

	// Todo: Validation of attachments (including downloading non-existent files)
	Route::get('attachment/{attachment}/download', ['as' => 'attachment.download', 'uses' => 'AttachmentsController@download']);

	Route::post('filemanagement/{schoolLocation}/class',['as' => 'filemanagement.uploadclass','uses' => 'FileManagementController@storeClassUpload']);
    Route::post('filemanagement/{schoolLocation}/test',['as' => 'filemanagement.uploadtest','uses' => 'FileManagementController@storeTestUpload']);

    Route::get('filemanagement/',['as' => 'filemanagement.index','uses' => 'FileManagementController@index']);
    Route::get('filemanagement/{fileManagement}',['as' => 'filemanagement.view','uses' => 'FileManagementController@show']);
    Route::get('filemanagement/{fileManagement}/download',['as' => 'filemanagement.download','uses' => 'FileManagementController@download']);
    Route::put('filemanagement/{fileManagement}',['as' => 'filemanagement.update','uses' => 'FileManagementController@update']);
    Route::get('filemanagement/statuses',['as' => 'filemanagement.statuses','uses' => 'FileManagementController@getStatuses']);

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

	Route::get('test_participant/{test_participant}/question_and_answer2019/{answer}', ['uses' => 'TestParticipants\Answers2019Controller@showQuestionAndAnswer']);

	// Education level
	Route::resource('education_level', 'EducationLevelsController', ['except' => ['create', 'edit']]);
	Route::get('school_location_education_level/{schoolLocation}','SchoolLocationEducationLevelsController@index');

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
    Route::post('/school_class/importStudents/{schoolLocation}/{schoolClass}','SchoolClassesStudentImportController@store')->name('school_classes.import');

    Route::get('school_class/list', ['as' => 'school_class.list', 'uses' => 'SchoolClassesController@lists']);
    Route::resource('school_class', 'SchoolClassesController', ['except' => ['create', 'edit']]);


    Route::get('invigilator/list', ['as' => 'invigilator.list', 'uses' => 'InvigilatorsController@lists']);

	Route::get('student', ['as' => 'student.index', 'uses' => 'StudentsController@index']);

	// Shortcuts for questions show
	Route::get('question/{question}/bg', ['as' => 'question.bg', 'uses' => 'QuestionsController@bg']);
	Route::resource('question', 'QuestionsController', ['only' => ['index', 'show']]);
    Route::resource('attachment', 'AttachmentsController', ['only' => ['index', 'show']]);

    Route::get('question/inlineimage/{image}',['uses' => 'QuestionsController@inlineimage']);

	Route::resource('attainment', 'AttainmentsController', ['only' => ['index', 'show']]);

	// Phase B
	Route::resource('answer_rating', 'AnswerRatingsController', ['except' => ['create', 'edit']]);

	Route::resource('answer', 'AnswersController', ['only' => ['index', 'show']]);

	// Users
	Route::get('user/{user}/profile_image', ['as' => 'user.profile_image', 'uses' => 'UsersController@profileImage']);
	Route::get('user/send_welcome_email', ['as' => 'user.send_welcome_email', 'uses' => 'UsersController@sendWelcomeEmail']);
	Route::resource('user', 'UsersController', ['except' => ['create', 'edit']]);
	Route::post('/tell_a_teacher', 'TellATeacherController@store');

	Route::put('user/update_password_for_user/{user}',['as' => 'user.update_password_for_user','uses' => 'UsersController@updatePasswordForUser']);
	Route::resource('teacher', 'TeachersController', ['except' => ['create', 'edit']]);

    Route::post('/teacher/import/schoollocation','TeachersController@import')->name('teacher.import');

    Route::post('/attainments/import','AttainmentImportController@import')->name('attainment.import');
    Route::post('/attainments_cito/import','AttainmentCitoImportController@import')->name('attainment_cito.import');
    Route::get('attainments/data','AttainmentCitoImportController@data')->name('attainment_cito.data');

    Route::get('demo_account/{user}', 'DemoAccountController@show')->name('demo_account.show');
    Route::put('demo_account/{user}', 'DemoAccountController@update')->name('demo_account.update');
    Route::get('demo_account/{user}/registration_completed', 'DemoAccountController@showRegistrationCompleted')->name('demo_account.registration_completed');
    Route::post('demo_account/notify_support_teacher_tries_to_upload', 'DemoAccountController@notifySupportTeacherTriesToUpload')->name('demo_account.notify_support_teacher_tries_to_upload');

    Route::put('user/switch_school_location/{user}','UsersController@switch_school_location')->name('user.switch_school_location');

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

	Route::get('admin/teacher_stats','AdminTeacherStatsController@index')->name('admin_teacher_stats');
    Route::get('qtiimport/data','QtiImportController@data')->name('qtiimport_data');
    Route::post('qtiimport/import','QtiImportController@store')->name('qtiimport_import');

    Route::get('qtiimportcito/data','QtiImportCitoController@data')->name('qtiimportcito_data');
    Route::post('qtiimportcito/import','QtiImportCitoController@store')->name('qtiimportcito_import');

    Route::get('qtiimportbatchcito/data','QtiImportBatchCitoController@data')->name('qtiimportbatchcito_data');
    Route::post('qtiimportbatchcito/import','QtiImportBatchCitoController@store')->name('qtiimportbatchcito_import');


    Route::post('testing', 'Testing\TestingController@store')->name('testing.store');

    Route::post('onboarding_wizard_report', 'OnboardingWizardReportController@store')->name('onboarding_wizard_report.store');
    Route::get('onboarding_wizard_report', 'OnboardingWizardReportController@show');


});
