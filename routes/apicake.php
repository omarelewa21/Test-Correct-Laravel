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
use Laravel\Dusk\Http\Controllers\UserController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use tcCore\Http\Controllers\Auth\AuthController;
use tcCore\Http\Controllers\Testing\TestingController;

// file name was api.php and is now apicake.php in order to make room for the direct access urls


Route::post('/do_we_need_captcha',[AuthController::class,'doWeNeedCaptcha'])->name('user.doWeNeedCaptcha');
Route::get('/auth/laravel_login_page', [AuthController::class, 'getLaravelLoginPage'])->name('auth.laravelLoginPage');
Route::get('/auth/laravel_entree_url', [AuthController::class, 'getLaravelEntreeUrl'])->name('auth.laravelEntreeUrl');

Route::get('/edu-k', 'EduK\HomeController@index');
Route::post('demo_account', 'DemoAccountController@store')->name('demo_account.store');
Route::get('config', 'ConfigController@show')->name('config.show');

Route::get('/', 'HomeController@index');

Route::get('/testing/selenium', [TestingController::class, 'seleniumState'])->name('testing.seleniumState');
Route::post('/testing/selenium', [TestingController::class, 'seleniumToggle'])->name('testing.seleniumToggle');
Route::post('/testing/testing', [TestingController::class, 'store'])->name('testing.store');


/* TEST MULTIPLE QUESTION STUFF */

Route::post('auth', ['uses' => 'Auth\AuthController@getApiKey']);
Route::post('send_password_reset', ['uses' => 'Auth\PasswordController@sendPasswordReset']);
Route::post('password_reset', ['uses' => 'Auth\PasswordController@passwordReset']);

Route::get('edu-ix/{ean}/{session_id}/{signature}', 'EduK\HomeController@create');
Route::post('edu-ix/{ean}/{session_id}/{edu_ix_signature}', 'EduK\HomeController@store');

Route::get('temporary_login/{tlid}', ['as' => 'user.temporary_login', 'uses'=>'UsersController@temporaryLogin']);

Route::get('check_for_deployment_maintenance',['uses' => 'DeploymentMaintenanceController@checkForMaintenance']);

Route::group(['middleware' => ['api', 'bindings']], function() {
    Route::get('info', [tcCore\Http\Controllers\InfoController::class, 'index']);
    Route::post('info/removeDashboardInfo/{info}', [tcCore\Http\Controllers\InfoController::class,'removeDashboardInfo']);
    Route::put('info/seenNewFeatures', [tcCore\Http\Controllers\InfoController::class,'seenNewFeatures']);
    Route::put('info/closedNewFeaturesMessage', [tcCore\Http\Controllers\InfoController::class,'closedNewFeaturesMessage']);
});

Route::group(['middleware' => ['api', 'dl', 'authorize', 'authorizeBinds', 'bindings']], function(){
    Route::post('/default_subjects_and_sections/import',[\tcCore\Http\Controllers\DefaultSubjectsAndSectionsController::class,'import'])->name('defaultSubjectsAndSections.import');
    Route::post('/school_school_locations/import',[\tcCore\Http\Controllers\SchoolAndSchoolLocationsImportController::class,'import'])->name('schoolAndSchoolLocations.import');
    Route::put('/add_default_subjects_and_sections_to_school_location/{schoolLocation}',[\tcCore\Http\Controllers\SchoolLocationsController::class,'addDefaultSubjectsAndSections'])->name('schoolLocation.addDefaultSubjectsAndSections');


    Route::post('/temporary-login',[tcCore\Http\Controllers\TemporaryLoginController::class,'create'])->name('auth.temporary-login.create');

    Route::get('info/{info}',[tcCore\Http\Controllers\InfoController::class,'show']);
    Route::post('info',[tcCore\Http\Controllers\InfoController::class,'store']);
    Route::put('info/{info}',[tcCore\Http\Controllers\InfoController::class,'update']);
    Route::delete('info/{info}',[tcCore\Http\Controllers\InfoController::class,'delete']);

    Route::get('role',[\tcCore\Http\Controllers\RolesController::class,'index']);

    Route::get('authors',['as' => 'authors','uses' => 'AuthorsController@index']);
    Route::get('/deployment',['uses' => 'DeploymentController@index']);
    Route::get('/deployment/{deployment}',['uses' => 'DeploymentController@show']);
    Route::post('/deployment',['uses' => 'DeploymentController@create']);
    Route::put('/deployment/{deployment}',['uses' => 'DeploymentController@update']);

    Route::get('/maintenance_whitelist_ip',['uses' => 'MaintenanceWhitelistIpController@index']);
    Route::get('/maintenance_whitelist_ip/{maintenanceWhitelistIp}',['uses' => 'MaintenanceWhitelistIpController@show']);
    Route::post('/maintenance_whitelist_ip',['uses' => 'MaintenanceWhitelistIpController@create']);
    Route::put('/maintenance_whitelist_ip/{maintenanceWhitelistIp}',['uses' => 'MaintenanceWhitelistIpController@update']);
    Route::delete('/maintenance_whitelist_ip/{maintenanceWhitelistIp}',['uses' => 'MaintenanceWhitelistIpController@delete']);

    // app_version_info
    Route::post('/app_version_info',['as' => 'app_version_info.store','uses' => 'AppVersionInfoController@store']);
	// Onboarding
    Route::post('/onboarding/registeruserstep',['as' => 'onboarding.register_userstep','uses' => 'OnboardingWizardController@registerUserStep']);
    Route::get('/onboarding/{user}/steps',['as' => 'onboarding.show_steps_for_user','uses' => 'OnboardingWizardController@showStepsForUser']);
    Route::put('/onboarding',['as' => 'onboarding.update','uses' => 'OnboardingWizardController@update']);

    // Tests + children
	Route::post('test/{test}/duplicate', ['as' => 'test.duplicate', 'uses' => 'TestsController@duplicate']);
	Route::resource('test', 'TestsController', ['except' => ['create', 'edit']]);
	Route::get('test_max_score/{test}','TestsController@maxScoreResponse')->name('test.max_score');
    Route::resource('cito_test','Cito\TestsController')->only(['index','show']);
    Route::resource('exam_test','Exam\TestsController')->only(['index','show']);

    Route::get('shared_sections','SharedSectionsController@index');

    Route::get('shared_section_test/{test}','SharedSections\TestsController@show');
    Route::get('shared_section_test','SharedSections\TestsController@index');
    Route::post('shared_section_test/{test}','SharedSections\TestsController@duplicate');

    Route::get('shared_sections/optional_school_locations/{section}','SharedSectionsController@optionalSchoolLocations');
    Route::get('shared_sections/{section}','SharedSectionsController@index');
    Route::post('shared_sections/{section}','SharedSectionsController@store');
    Route::delete('shared_sections/{section}/{school_location}','SharedSectionsController@destroy');

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

    Route::get('filemanagement/users',['as' => 'filemanagement.users','uses' => 'FileManagementUsersController@index']);
    Route::get('filemanagement/schoollocations',['as' => 'filemanagement.schoollocations','uses' => 'FileManagementSchoolLocationsController@index']);
    Route::get('filemanagement/educationlevels',['as' => 'filemanagement.educationlevels','uses' => 'FileManagementEducationLevelsController@index']);
    Route::get('filemanagement/statuses',['as' => 'filemanagement.statuses','uses' => 'FileManagementController@getStatuses']);

    Route::get('filemanagement/',['as' => 'filemanagement.index','uses' => 'FileManagementController@index']);
    Route::get('filemanagement/form_id',['as' => 'filemanagement.form_id','uses' => 'FileManagementController@getFormId']);
    Route::get('filemanagement/{fileManagement}',['as' => 'filemanagement.view','uses' => 'FileManagementController@show']);
    Route::get('filemanagement/{fileManagement}/download',['as' => 'filemanagement.download','uses' => 'FileManagementController@download']);
    Route::post('filemanagement/{fileManagement}/duplicate_test_to_school',['as' => 'filemanagement.duplicate-test-to-school','uses' => 'FileManagementController@duplicateTestToSchool']);
    Route::put('filemanagement/{fileManagement}',['as' => 'filemanagement.update','uses' => 'FileManagementController@update']);
    Route::get('test_take/get_surveillance_data', 'SurveillanceController@index')->name('test_take.get_surveillance_data');
    Route::get('test_take/bust_surveillance_cache', 'SurveillanceController@destroy')->name('test_take.bust_surveillance_cache');

	// Test take + children
	Route::get('test_take/{test_take}/export', ['as' => 'test_take.export', 'uses' => 'TestTakesController@exportRttiCsvFile']);
	Route::get('test_take/{test_take}/export-grades-csv', ['as' => 'test_take.export-csv', 'uses' => 'TestTakesController@exportGradesCsvFile']);
	Route::post('test_take/{test_take}/normalize', ['as' => 'test_take.normalize', 'uses' => 'TestTakesController@normalize']);
	Route::post('test_take/{test_take}/next_question', ['as' => 'test_take.next_question', 'uses' => 'TestTakesController@nextQuestion']);
	Route::resource('test_take', 'TestTakesController', ['except' => ['create', 'edit']]);
    Route::put('test_take/{test_take}/archive','TestTakesController@archive')->name('test_take.archive');
	Route::put('test_take/{test_take}/un-archive','TestTakesController@unarchive')->name('test_take.un_archive');
	Route::get('test_take/{test_take_uuid}/grading','TestTakesController@showForGrading')->name('test_take.grading');

    Route::post('test_take/{test_take}/rtti_export', ['as' => 'test_take.rtti_export', 'uses' => 'TestTakes\TestTakeRttiExportController@store']);
	Route::put('test_take/{test_take}/update-status-to-discussed','TestTakesController@updateStatusToDiscussed')->name('test_take.updateStatusToDiscussed');
	Route::get('test_take/{test_take_uuid}/grading','TestTakesController@showForGrading')->name('test_take.grading');

	Route::post('test/{test}/with_temporary_login',  'TestsController@withTemporaryLogin')->name('test.with_short_code');
    Route::post('test/answer_model/{test}/with_temporary_login',  'TestsController@answerModelwithTemporaryLogin')->name('test_answer_model.with_short_code');
    Route::post('test/pdf/{test}/with_temporary_login',  'TestsController@pdfWithTemporaryLogin')->name('test_pdf.with_short_code');
    Route::post('test/pdf-attachments/{test}/with_temporary_login',  'TestsController@pdfPdfAttachmentsWithTemporaryLogin')->name('test_pdf_attachments.with_short_code');
	Route::post('test_take/{test_take}/with_temporary_login',  'TestTakesController@withTemporaryLogin')->name('test_take.with_short_code');
	Route::post('test_take/pdf/{test_take}/with_temporary_login',  'TestTakesController@pdfWithTemporaryLogin')->name('test_take_pdf.with_short_code');
    Route::post('test_take/answers/{test_take}/with_temporary_login',  'TestTakesController@AnswersWithTemporaryLogin')->name('test_take_answers.with_short_code');

	// Test take children
	Route::post('test_take/{test_take_id}/test_participant/{test_participant}/heartbeat', ['as' => 'test_take.test_participant.heartbeat', 'uses' => 'TestTakes\TestParticipantsController@heartbeat']);
	Route::resource('test_take.test_participant', 'TestTakes\TestParticipantsController', ['except' => ['create', 'edit']]);
	Route::resource('test_take.test_take_event', 'TestTakes\TestTakeEventsController', ['except' => ['create', 'edit']]);

	Route::resource('test_take_event_type', 'TestTakeEventTypesController', ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);

	Route::get('test_take_max_score/{test_take}','TestTakesController@maxScoreResponse')->name('test_take.max_score');


    Route::get('test_take/{test_take}/attainment/analysis','TestTakes\TestTakeAttainmentAnalysisController@index')->name('test_take_attainment_analysis.index');
    Route::get('test_take/{test_take}/attainment/{attainment}/analysis','TestTakes\TestTakeAttainmentAnalysisController@show')->name('test_take_attainment_analysis.show');
    Route::get('test_take/{test_take}/learning_goal/{learning_goal}/analysis','TestTakes\TestTakeAttainmentAnalysisController@show')->name('test_take_attainment_analysis.show_learning_goal');

    Route::get('test_participant/{test_participant}/answers_status_and_questions2019',['uses' => 'TestParticipants\Answers2019Controller@getAnswersStatusAndQuestions']);
	Route::get('test_participant/{test_participant}/{test_take}/answers_status_and_test_take2019',['uses' => 'TestParticipants\Answers2019Controller@getAnswersStatusAndTestTake']);
    Route::get('test_participant/{test_participant}/answers_and_status2019',['uses' => 'TestParticipants\Answers2019Controller@getAnswersAndStatus']);

    Route::get('test_participant/drawing_answer_url/{answer}',['uses' => 'TestParticipants\AnswersController@getDrawingAnswerUrl']);

    // feedback
    Route::get('test_participant/feedback/{test_participant}/{question}', 'TestParticipants\AnswersController@loadFeedback');
    Route::get('test_participant/feedback_by_answer/{answer}', 'TestParticipants\AnswersController@loadFeedbackByAnswer');
    Route::post('test_participant/feedback/{answer}', 'TestParticipants\AnswersController@saveFeedback');
    Route::delete('test_participant/feedback/{feedback_id}', 'TestParticipants\AnswersController@deleteFeedback')
	;

    Route::get('answers/drawing_answer/{answer}',['uses' => 'AnswersController@showDrawing']);

    Route::get('answer/{answer}/test_take',['uses' => 'AnswersController@getTestTake']);

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
	Route::get('school_year_active', ['as' => 'school_year_active', 'uses' => 'SchoolYearsController@activeSchoolYear']);
	Route::resource('school_year', 'SchoolYearsController', ['except' => ['create', 'edit']]);


	Route::resource('period', 'PeriodsController', ['except' => ['create', 'edit']]);

	// Subjects
	Route::resource('subject', 'SubjectsController', ['except' => ['create', 'edit']]);
	Route::resource('cito_subject','Cito\SubjectsController')->only(['index']);
    Route::resource('exam_subject','Exam\SubjectsController')->only(['index']);

	// Test kinds
	Route::get('test_kind/list', ['as' => 'test_kind.list', 'uses' => 'TestKindsController@lists']);
	Route::resource('test_kind', 'TestKindsController', ['except' => ['create', 'edit']]);

	// Needed lookups
    Route::post('/school_class/importStudents/{schoolLocation}/{schoolClass}','SchoolClassesStudentImportController@store')->name('school_classes.import');
    Route::post('/school_class/importStudentsWithClasses/{schoolLocation}','SchoolClassesStudentImportController@store')->name('school_classes.import_with_classes');
    Route::put('/school_class/update_with_education_levels_for_main_classes', 'SchoolClassesController@updateWithEducationLevelsForMainClasses')->name('school_classes.update_with_education_levels_for_main_classes');
    Route::put('/school_class/update_with_education_levels_for_cluster_classes', 'SchoolClassesController@updateWithEducationLevelsForClusterClasses')->name('school_classes.update_with_education_levels_for_cluster_classes');
    Route::put('/school_class/reset_passwords/{schoolClass}', 'SchoolClassesController@resetPasswords')->name('school_classes.reset_passwords');

    Route::get('school_class/list', ['as' => 'school_class.list', 'uses' => 'SchoolClassesController@lists']);
    Route::get('school_class/forUser/{user}', 'SchoolClassesController@showForUser')->name('school_classes.for_user');
    Route::resource('school_class', 'SchoolClassesController', ['except' => ['create', 'edit']]);

    Route::delete('school_class_mentor/{schoolClass}/{userUuid}','SchoolClassesController@deleteMentor')->name('school_class_mentor.delete');

    Route::get('invigilator/list', ['as' => 'invigilator.list', 'uses' => 'InvigilatorsController@lists']);

	Route::get('student', ['as' => 'student.index', 'uses' => 'StudentsController@index']);

	// Shortcuts for questions show
	Route::get('question/{question}/bg', ['as' => 'question.bg', 'uses' => 'QuestionsController@bg']);
	Route::resource('question', 'QuestionsController', ['only' => ['index', 'show']]);
    Route::resource('attachment', 'AttachmentsController', ['only' => ['index', 'show']]);

    Route::get('question/inlineimage/{image}',['uses' => 'QuestionsController@inlineimage']);
    Route::get('/drawing-question/{answerUuid}/given-answer-png', [tcCore\Http\Controllers\QuestionsController::class, 'getDrawingQuestionGivenAnswerPng'])->name('api-c.drawing-question.givenanswerpng');
    Route::get('/drawing-question/{drawingQuestion}/correction-model', [tcCore\Http\Controllers\QuestionsController::class, 'drawingQuestionCorrectionModelPng'])->name('api-c.drawing-question.correction-model');
    Route::get('/drawing-question/{drawing_question}/background-image', [tcCore\Http\Controllers\QuestionsController::class, 'getDrawingQuestionBackgroundImageUpdated'])->name('api-c.drawing-question.background-image');

    Route::resource('attainment', 'AttainmentsController', ['only' => ['index', 'show']]);

	// Phase B
	Route::resource('answer_rating', 'AnswerRatingsController', ['except' => ['create', 'edit']]);

	Route::resource('answer', 'AnswersController', ['only' => ['index', 'show']]);

	// Users
    Route::get('user/{user}/verify_password', 'UsersController@verifyPassword')->name('user.verify_password');
    Route::get('user/{user}/profile_image', ['as' => 'user.profile_image', 'uses' => 'UsersController@profileImage']);
    Route::get('user/send_welcome_email', ['as' => 'user.send_welcome_email', 'uses' => 'UsersController@sendWelcomeEmail']);
    Route::get('user/is_account_verified', ['as' => 'user.is_account_verified', 'uses' => 'UsersController@isAccountVerified']);
    Route::post('user/toggle_account_verified/{user}', ['as' => 'user.toggle_account_verified', 'uses' => 'UsersController@toggleAccountVerified']);
    Route::post('/user/import/{type}','UsersController@import')->name('user.import');

    Route::get('/user/{user}/time_sensitive_records','UsersController@getTimeSensitiveUserRecords')->name('user.getTimeSensitiveUserRecords');
    Route::put('/user/{user}/general_terms_accepted','UsersController@setGeneralTermsLogAcceptedAtForUser')->name('user.setGeneralTermsLogAcceptedAtForUser');

    Route::get('/user/{user}/return_to_laravel_url','UsersController@getReturnToLaravelUrl')->name('user.getReturnToLaravelUrl');
    Route::get('/user/toetsenbakkers','UsersController@toetsenbakkers')->name('user.toetsenbakkers');

    Route::put('user/resend_onboarding_welcome_email', ['as' => 'user.send_onboarding_welcome_email', 'uses' => 'UsersController@sendOnboardingWelcomeEmail']);
    Route::resource('user', 'UsersController', ['except' => ['create', 'edit']]);
    Route::post('/tell_a_teacher', 'TellATeacherController@store')->name('tell_a_teacher.store');

    Route::put('user/update_password_for_user/{user}',['as' => 'user.update_password_for_user','uses' => 'UsersController@updatePasswordForUser']);

    Route::put('/teacher/update_with_subjects_for_cluster_classes', 'TeachersController@updateWithSubjectsForClusterClasses')->name('teacher.update_with_subjects_for_cluster_classes');

    Route::get('teacher/has_incomplete_import', 'TeachersController@hasIncompleteImport')->name('teacher.has_incomplete_import');
    Route::get('account_manager/has_incomplete_import', 'TeachersController@hasIncompleteImport')->name('account_manager.has_incomplete_import');
    Route::resource('teacher', 'TeachersController', ['except' => ['create', 'edit']]);

    Route::post('/teacher/import/schoollocation','TeachersController@import')->name('teacher.import');
    Route::get('/teacher/school_location_teacher_users/{school_location}','TeachersController@getSchoolLocationTeacherUser')->name('teacher.school-location-teacher-users');

    Route::post('/attainments/import','AttainmentImportController@import')->name('attainment.import');
    Route::post('/attainments_cito/import','AttainmentCitoImportController@import')->name('attainment_cito.import');
    Route::get('attainments/data','AttainmentCitoImportController@data')->name('attainment_cito.data');
    Route::post('/attainments/upload','AttainmentImportController@upload')->name('attainment.upload');
    Route::get('/attainments/export','AttainmentExportController@export')->name('attainment.export');
    Route::post('/learning_goals/upload','LearningGoalImportController@upload')->name('learning_goal.upload');
    Route::get('/learning_goals/export','LearningGoalExportController@export')->name('learning_goal.export');

    Route::get('demo_account/{user}', 'DemoAccountController@show')->name('demo_account.show');
    Route::put('demo_account/{user}', 'DemoAccountController@update')->name('demo_account.update');
    Route::get('demo_account/{user}/registration_completed', 'DemoAccountController@showRegistrationCompleted')->name('demo_account.registration_completed');
    Route::post('demo_account/notify_support_teacher_tries_to_upload', 'DemoAccountController@notifySupportTeacherTriesToUpload')->name('demo_account.notify_support_teacher_tries_to_upload');

    Route::put('user/move_school_location/{user}','UsersController@move_school_location')->name('user.move_school_location');

	// Sales organization
	Route::resource('sales_organization', 'SalesOrganizationsController', ['except' => ['create', 'edit']]);

	// Umbrella organisation + child
	Route::resource('umbrella_organization', 'UmbrellaOrganizationsController', ['except' => ['create', 'edit']]);

	Route::resource('school', 'SchoolsController', ['except' => ['create', 'edit']]);
    Route::get('school_location/is_allowed_new_player_access', 'SchoolLocationsController@isAllowedNewPlayerAccess')->name('school_location.is_allowed_new_player_access');
    Route::get('school_location/get_available_school_location_options', 'SchoolLocationsController@getAvailableSchoolLocationOptions')->name('school_location.get_available_school_location_options');
    Route::get('school_location/{school_location_id}/get_lvs_type', 'SchoolLocationsController@getLvsType')->name('school_location.get_lvs_type');
    // School children
    Route::resource('school_location', 'SchoolLocationsController', ['except' => ['create', 'edit']]);
    Route::get('school_location/{id}/get_by_id', 'SchoolLocationsController@showById')->name('school_location.showById');
    // School location children
    Route::resource('school_location.school_class', 'SchoolLocations\SchoolClassesController', ['except' => ['create', 'edit']]);
    Route::resource('school_location.school_location_ip', 'SchoolLocations\SchoolLocationIpsController', ['except' => ['create', 'edit']]);
    Route::resource('school_location.section', 'SchoolLocations\SectionsController', ['except' => ['create', 'edit']]);
    Route::resource('school_location.license', 'SchoolLocations\LicensesController', ['except' => ['create', 'edit']]);

    Route::resource('school_year', 'SchoolYearsController', ['except' => ['create', 'edit']]);
    Route::resource('school_year.period', 'SchoolYears\PeriodsController', ['except' => ['create', 'edit']]);

    Route::resource('section', 'SectionsController', ['except' => ['create', 'edit']]);
    Route::resource('subject', 'SubjectsController', ['except' => ['create', 'edit']]);

    Route::put('message/mark_read/{message}', 'MessageController@markRead')->name('message.mark_read');
    Route::resource('message', 'MessageController', ['except' => ['create', 'edit']]);

    Route::resource('address', 'AddressesController', ['except' => ['create', 'edit']]);
    Route::resource('contact', 'ContactsController', ['except' => ['create', 'edit']]);
    Route::resource('grading_scale', 'GradingScalesController', ['except' => ['create', 'edit']]);

    Route::resource('base_subject', 'BaseSubjectsController', ['only' => ['index']]);
    Route::resource('my_base_subject', 'MyBaseSubjectsController', ['only' => ['index']]);

    Route::resource('tag', 'TagsController', ['only' => ['index', 'show']]);

    Route::get('admin/teacher_stats','AdminTeacherStatsController@index')->name('admin_teacher_stats');
    Route::get('qtiimport/data','QtiImportController@data')->name('qtiimport_data');
    Route::post('qtiimport/import','QtiImportController@store')->name('qtiimport_import');

    Route::get('qtiimportcito/data','QtiImportCitoController@data')->name('qtiimportcito_data');
    Route::post('qtiimportcito/import','QtiImportCitoController@store')->name('qtiimportcito_import');

    Route::get('qtiimportbatchcito/data','QtiImportBatchCitoController@data')->name('qtiimportbatchcito_data');
    Route::post('qtiimportbatchcito/import','QtiImportBatchCitoController@store')->name('qtiimportbatchcito_import');

    Route::get('rttiimport/data','RttiImportController@data')->name('rttiimport_data');
    Route::post('rttiimport/import','RttiImportController@store')->name('rttiimport_import');

    //Route::post('testing', 'Testing\TestingController@store')->name('testing.store');

    Route::post('marketing_report', 'OnboardingWizardReportController@store')->name('onboarding_wizard_report.store');
    Route::get('marketing_report', 'OnboardingWizardReportController@show');
    Route::post('school_location_report', 'SchoolLocationReportController@store')->name('school_location_report.store');
    Route::get('school_location_report', 'SchoolLocationReportController@show');

    Route::post('search_filter','SearchFiltersController@store')->name('search_filter.store');
    Route::put('search_filter/{uuid}','SearchFiltersController@update')->name('search_filter.update');
    Route::get('search_filter/{key?}','SearchFiltersController@show')->name('search_filter.show');
    Route::delete('search_filter/{uuid}','SearchFiltersController@delete')->name('search_filter.delete');
    Route::put('search_filter/{uuid}/set_active','SearchFiltersController@setActive')->name('search_filter.set_active');
    Route::put('search_filter/{uuid}/deactive','SearchFiltersController@deactive')->name('search_filter.deactive');

    Route::get('school_location_user', 'SchoolLocationUsersController@index')->name('school_location_user.index');
    Route::put('school_location_user', 'SchoolLocationUsersController@update')->name('school_location_user.update');
    Route::post('school_location_user', 'SchoolLocationUsersController@store')->name('school_location_user.store');
    Route::delete('school_location_user', 'SchoolLocationUsersController@delete')->name('school_location_user.delete');

    Route::get('school_location_user/existing_teachers', 'SchoolLocationUsersController@getExistingTeachers')->name('school_location_user.get_existing_teachers');

    Route::get('shortcode','Api\ShortcodeController@store')->name('shortcode.store');
    Route::put('shortcodeclick/{shortcodeClick}','Api\ShortcodeClickController@update')->name('shortcodeClick.update');

//    Route::get('config/{variable_name}','ConfigController@show')->name('config.show');
    // goes to the web part
    // Route::get('tlc/{shortcode}','ShortcodeController@registerClickAndRedirect')->name('shortcode.registerAndRedirect');
    Route::get('test_participant/{test_take}/is_allowed_inbrowser_testing','TestTakes\TestParticipantsController@is_allowed_inbrowser_testing')->name('testparticipant.is_allowed_inbrowser_testing.show');
    Route::get('test_take/{test_take}/is_allowed_inbrowser_testing','TestTakesController@isAllowedInbrowserTesting')->name('test_takes.is_allowed_inbrowser_testing.show');
    Route::put('test_take/{test_take}/test_participant/{test_participant}/toggle_inbrowser_testing','TestTakes\TestParticipantsController@toggle_inbrowser_testing')->name('testparticipant.is_allowed_inbrowser_testing.update');
    Route::get('test_take/{test_take}/has_carousel_question','TestTakesController@hasCarouselQuestion')->name('test_takes.has_carousel_question');
    Route::put('test_take/{test_take}/toggle_inbrowser_testing_for_all_participants','TestTakesController@toggleInbrowserTestingForAllParticipants')->name('test_takes.toggle_inbrowser_testing_for_all_participants');

    Route::post('/convert/html/pdf','PdfController@HtmlToPdf')->name('convert.htmltopdf');
    Route::get('/convert/settings/{setting}','PdfController@getSetting')->name('convert.getSetting');

    Route::put('support/register_take_over/{user}','SupportTakeOverLogController@store')->name('support_take_over_log.store');
    Route::get('support/show/{user}','SupportTakeOverLogController@show')->name('support_take_over_log.show');
    Route::get('support/index','SupportTakeOverLogController@index')->name('support_take_over_log.index');
    Route::post('/user/{user}/update_trial_date','UsersController@updateTrialDate')->name('user.update_trial_date');
    Route::post('/user/{user}/create_trial_record','UsersController@createTrialRecord')->name('user.create_trial_record');
    // UPDAETE USER FEATURE IN USER SYSTEM SETTING TABLE
    Route::put('/user/{user}/update_user_feature', 'UsersController@updateUserFeature');
    Route::get('/user/{user}/get_user_system_settings', 'UsersController@getUserSystemSettings')->name('get_user_system_settings.getUserSystemSettings');
});
