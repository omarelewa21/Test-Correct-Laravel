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

Route::get('/', ['uses' => 'HomeController@index']);

/* TEST MULTIPLE QUESTION STUFF */
Route::get('/testing/{id}', ['uses' => 'HomeController@test']);

Route::post('auth', ['uses' => 'Auth\AuthController@getApiKey']);
Route::post('send_password_reset', ['uses' => 'Auth\PasswordController@sendPasswordReset']);
Route::post('password_reset', ['uses' => 'Auth\PasswordController@passwordReset']);

Route::group(['middleware' => ['api', 'dl', 'authorize', 'authorizeBinds']], function(){
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
	Route::post('test_take/{test_take}/test_participant/{test_participant}/heartbeat', ['as' => 'test_take.test_participant.heartbeat', 'uses' => 'TestTakes\TestParticipantsController@heartbeat']);
	Route::resource('test_take.test_participant', 'TestTakes\TestParticipantsController', ['except' => ['create', 'edit']]);
	Route::resource('test_take.test_take_event', 'TestTakes\TestTakeEventsController', ['except' => ['create', 'edit']]);

	Route::resource('test_take_event_type', 'TestTakeEventTypesController', ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);

	// Test participant children
	Route::resource('test_participant.answer', 'TestParticipants\AnswersController', ['except' => ['create', 'edit']]);

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
//
//Route::bind('address', function($id) {
//	try {
//		return tcCore\Address::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Address not found');
//	}
//});
//
//Route::bind('answer_rating', function($id) {
//	try {
//		return tcCore\AnswerRating::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Answer rating not found');
//	}
//});
//
//Route::bind('answer', function($id) {
//	try {
//		return tcCore\Answer::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Answer not found');
//	}
//});
//
//Route::bind('attachment', function($id) {
//	try {
//		return tcCore\Attachment::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Attachment not found');
//	}
//});
//
//Route::bind('attainment', function($id) {
//	try {
//		return tcCore\Attainment::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Attainment not found');
//	}
//});
//
//Route::bind('base_subject', function($id) {
//	try {
//		return tcCore\Question::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Base subject not found');
//	}
//});
//
//Route::bind('completion_question_answer', function($id) {
//	try {
//		return tcCore\CompletionQuestionAnswer::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Completion question answer not found');
//	}
//});
//
//Route::bind('completion_question', function($id) {
//	try {
//		return tcCore\CompletionQuestion::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Completion question not found');
//	}
//});
//
//Route::bind('contact', function($id) {
//	try {
//		return tcCore\Contact::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Contact not found');
//	}
//});
//
//Route::bind('drawing_question', function($id) {
//	try {
//		return tcCore\DrawingQuestion::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Drawing question not found');
//	}
//});
//
//Route::bind('education_level', function($id) {
//	try {
//		return tcCore\EducationLevel::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Education level not found');
//	}
//});
//
//Route::bind('grading_score', function($id) {
//	try {
//		return tcCore\GradingScale::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Grading scale not found');
//	}
//});
//
//Route::bind('group_question', function($id) {
//	try {
//		return tcCore\GroupQuestion::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Group question not found');
//	}
//});
//
////Route::bind('group_question_question', function($id) {
////	try {
////		return tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager::getInstance($id);
////	} catch(ModelNotFoundException $e) {
////		throw new NotFoundHttpException('Group question question path not found');
////	}
////});
//
//Route::bind('group_question_question_id', function($id) {
//	try {
//		return tcCore\GroupQuestionQuestion::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Group question question not found');
//	}
//});
//
///**
//Route::bind('invigilator', function($id) {
//try {
//return tcCore\Invigilator::findOrFail($id);
//} catch(ModelNotFoundException $e) {
//throw new NotFoundHttpException('Invigilator not found');
//}
//});
//*/
//
//Route::bind('license', function($id) {
//	try {
//		return tcCore\License::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('License not found');
//	}
//});
//
//Route::bind('matching_question_answer', function($id) {
//	try {
//		return tcCore\MatchingQuestionAnswer::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Matching question answer not found');
//	}
//});
//
//Route::bind('matching_question', function($id) {
//	try {
//		return tcCore\MatchingQuestion::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Matching question not found');
//	}
//});
//
//Route::bind('message', function($id) {
//	try {
//		return tcCore\Message::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Message not found');
//	}
//});
//
//Route::bind('multiple_choice_question_answer', function($id) {
//	try {
//		return tcCore\MultipleChoiceQuestionAnswer::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Multiple choice question answer not found');
//	}
//});
//
//Route::bind('multiple_choice_question', function($id) {
//	try {
//		return tcCore\MultipleChoiceQuestion::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Multiple choice question not found');
//	}
//});
//
//Route::bind('open_question', function($id) {
//	try {
//		return tcCore\OpenQuestion::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Open question not found');
//	}
//});
//
//Route::bind('period', function($id) {
//	try {
//		return tcCore\Period::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Period not found');
//	}
//});
//
//Route::bind('question', function($id) {
//	try {
//		return tcCore\Question::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Question not found');
//	}
//});
//
//Route::bind('ranking_question_answer', function($id) {
//	try {
//		return tcCore\RankingQuestionAnswer::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Ranking question answer not found');
//	}
//});
//
//Route::bind('ranking_question', function($id) {
//	try {
//		return tcCore\RankingQuestion::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Ranking question not found');
//	}
//});
//
//Route::bind('role', function($id) {
//	try {
//		return tcCore\Role::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Role not found');
//	}
//});
//
//Route::bind('sales_organisation', function($id) {
//	try {
//		return tcCore\SalesOrganization::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Sales organization not found');
//	}
//});
//
//Route::bind('school_class', function($id) {
//	try {
//		return tcCore\SchoolClass::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('School class not found');
//	}
//});
//
//Route::bind('school_location', function($id) {
//	try {
//		return tcCore\SchoolLocation::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('School location not found');
//	}
//});
//
//Route::bind('school_location_ip', function($id) {
//	try {
//		return tcCore\SchoolLocationIp::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('School location ip not found');
//	}
//});
//
//Route::bind('school_year', function($id) {
//	try {
//		return tcCore\SchoolYear::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('School year not found');
//	}
//});
//
//Route::bind('school', function($id) {
//	try {
//		return tcCore\School::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('School not found');
//	}
//});
//
//Route::bind('section', function($id) {
//	try {
//		return tcCore\Section::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Section not found');
//	}
//});
//
///**
//Route::bind('student', function($id) {
//	try {
//		return tcCore\Student::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Student not found');
//	}
//});
//*/
//
//Route::bind('subject', function($id) {
//	try {
//		return tcCore\Subject::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Subject not found');
//	}
//});
//
//Route::bind('tag', function($id) {
//	try {
//		return tcCore\Tag::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Tag not found');
//	}
//});
//
//
//Route::bind('teacher', function($id) {
//	try {
//		return tcCore\Teacher::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Teacher not found');
//	}
//});
//
//
//
//Route::bind('test_kind', function($id) {
//	try {
//		return tcCore\TestKind::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Test kind not found');
//	}
//});
//
//Route::bind('test_participant', function($id) {
//	try {
//		return tcCore\TestParticipant::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Test participant not found');
//	}
//});
//
//Route::bind('test_question', function($id) {
//	try {
//		return tcCore\TestQuestion::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Test question not found');
//	}
//});
//
//Route::bind('test_take_event', function($id) {
//	try {
//		return tcCore\TestTakeEvent::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Test take event not found');
//	}
//});
//
//Route::bind('test_take_event_type', function($id) {
//	try {
//		return tcCore\TestTakeEventType::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Test take event type not found');
//	}
//});
//
//Route::bind('test_take_status', function($id) {
//	try {
//		return tcCore\TestTakeStatus::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Test take status not found');
//	}
//});
//
//Route::bind('test_take', function($id) {
//	try {
//		return tcCore\TestTake::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Test take not found');
//	}
//});
//
//Route::bind('test', function($id) {
//	try {
//		return tcCore\Test::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Test not found');
//	}
//});
//
//Route::bind('umbrella_organization', function($id) {
//	try {
//		return tcCore\UmbrellaOrganization::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('Umbrella organization not found');
//	}
//});
//
///**
//Route::bind('user_role', function($id) {
//	try {
//		return tcCore\UserRole::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('User role not found');
//	}
//});
//*/
//
//Route::bind('user', function($id) {
//	try {
//		return tcCore\User::findOrFail($id);
//	} catch(ModelNotFoundException $e) {
//		throw new NotFoundHttpException('User not found');
//	}
//});