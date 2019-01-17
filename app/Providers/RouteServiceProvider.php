<?php namespace tcCore\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * This namespace is applied to the controller routes in your routes file.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'tcCore\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function boot(Router $router)
	{
		parent::boot($router);


		$router->model('address','tcCore\Address', function() {
			throw new NotFoundHttpException('Address not found');
		});

		$router->model('answer_rating','tcCore\AnswerRating', function() {
			throw new NotFoundHttpException('Answer rating not found');
		});

		$router->model('answer','tcCore\Answer', function() {
			throw new NotFoundHttpException('Answer not found');
		});

		$router->model('attachment','tcCore\Attachment', function() {
			throw new NotFoundHttpException('Attachment not found');
		});

		$router->model('attainment','tcCore\Attainment', function() {
			throw new NotFoundHttpException('Attainment not found');
		});

		$router->model('base_subject','tcCore\Question', function() {
			throw new NotFoundHttpException('Base subject not found');
		});

		$router->model('completion_question_answer','tcCore\CompletionQuestionAnswer', function() {
			throw new NotFoundHttpException('Completion question answer not found');
		});

		$router->model('completion_question','tcCore\CompletionQuestion', function() {
			throw new NotFoundHttpException('Completion question not found');
		});

		$router->model('contact','tcCore\Contact', function() {
			throw new NotFoundHttpException('Contact not found');
		});

		$router->model('drawing_question','tcCore\DrawingQuestion', function() {
			throw new NotFoundHttpException('Drawing question not found');
		});

		$router->model('education_level','tcCore\EducationLevel', function() {
			throw new NotFoundHttpException('Education level not found');
		});

		$router->model('grading_score','tcCore\GradingScale', function() {
			throw new NotFoundHttpException('Grading scale not found');
		});

		$router->model('group_question','tcCore\GroupQuestion', function() {
			throw new NotFoundHttpException('Group question not found');
		});

		$router->model('group_question_question','tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager', function() {
			throw new NotFoundHttpException('Group question question path not found');
		});

		$router->model('group_question_question_id','tcCore\GroupQuestionQuestion', function() {
			throw new NotFoundHttpException('Group question question not found');
		});

		/**
		$router->model('invigilator','tcCore\Invigilator', function() {
		throw new NotFoundHttpException('Invigilator not found');
		});
		 */

		$router->model('license','tcCore\License', function() {
			throw new NotFoundHttpException('License not found');
		});

		$router->model('matching_question_answer','tcCore\MatchingQuestionAnswer', function() {
			throw new NotFoundHttpException('Matching question answer not found');
		});

		$router->model('matching_question','tcCore\MatchingQuestion', function() {
			throw new NotFoundHttpException('Matching question not found');
		});

		$router->model('message','tcCore\Message', function() {
			throw new NotFoundHttpException('Message not found');
		});

		$router->model('multiple_choice_question_answer','tcCore\MultipleChoiceQuestionAnswer', function() {
			throw new NotFoundHttpException('Multiple choice question answer not found');
		});

		$router->model('multiple_choice_question','tcCore\MultipleChoiceQuestion', function() {
			throw new NotFoundHttpException('Multiple choice question not found');
		});

		$router->model('open_question','tcCore\OpenQuestion', function() {
			throw new NotFoundHttpException('Open question not found');
		});

		$router->model('period','tcCore\Period', function() {
			throw new NotFoundHttpException('Period not found');
		});

		$router->model('question','tcCore\Question', function() {
			throw new NotFoundHttpException('Question not found');
		});

		$router->model('ranking_question_answer','tcCore\RankingQuestionAnswer', function() {
			throw new NotFoundHttpException('Ranking question answer not found');
		});

		$router->model('ranking_question','tcCore\RankingQuestion', function() {
			throw new NotFoundHttpException('Ranking question not found');
		});

		$router->model('role','tcCore\Role', function() {
			throw new NotFoundHttpException('Role not found');
		});

		$router->model('sales_organisation','tcCore\SalesOrganization', function() {
			throw new NotFoundHttpException('Sales organization not found');
		});

		$router->model('school_class','tcCore\SchoolClass', function() {
			throw new NotFoundHttpException('School class not found');
		});

		$router->model('school_location','tcCore\SchoolLocation', function() {
			throw new NotFoundHttpException('School location not found');
		});

		$router->model('school_location_ip','tcCore\SchoolLocationIp', function() {
			throw new NotFoundHttpException('School location ip not found');
		});

		$router->model('school_year','tcCore\SchoolYear', function() {
			throw new NotFoundHttpException('School year not found');
		});

		$router->model('school','tcCore\School', function() {
			throw new NotFoundHttpException('School not found');
		});

		$router->model('section','tcCore\Section', function() {
			throw new NotFoundHttpException('Section not found');
		});

		/**
		$router->model('student','tcCore\Student', function() {
		throw new NotFoundHttpException('Student not found');
		});
		 */

		$router->model('subject','tcCore\Subject', function() {
			throw new NotFoundHttpException('Subject not found');
		});

		$router->model('tag','tcCore\Tag', function() {
			throw new NotFoundHttpException('Tag not found');
		});


		$router->model('teacher','tcCore\Teacher', function() {
			throw new NotFoundHttpException('Teacher not found');
		});



		$router->model('test_kind','tcCore\TestKind', function() {
			throw new NotFoundHttpException('Test kind not found');
		});

		$router->model('test_participant','tcCore\TestParticipant', function() {
			throw new NotFoundHttpException('Test participant not found');
		});

		$router->model('test_question','tcCore\TestQuestion', function() {
			throw new NotFoundHttpException('Test question not found');
		});

		$router->model('test_take_event','tcCore\TestTakeEvent', function() {
			throw new NotFoundHttpException('Test take event not found');
		});

		$router->model('test_take_event_type','tcCore\TestTakeEventType', function() {
			throw new NotFoundHttpException('Test take event type not found');
		});

		$router->model('test_take_status','tcCore\TestTakeStatus', function() {
			throw new NotFoundHttpException('Test take status not found');
		});

		$router->model('test_take','tcCore\TestTake', function() {
			throw new NotFoundHttpException('Test take not found');
		});

		$router->model('test','tcCore\Test', function() {
			throw new NotFoundHttpException('Test not found');
		});

		$router->model('umbrella_organization','tcCore\UmbrellaOrganization', function() {
			throw new NotFoundHttpException('Umbrella organization not found');
		});

		/**
		$router->model('user_role','tcCore\UserRole', function() {
		throw new NotFoundHttpException('User role not found');
		});
		 */

		$router->model('user','tcCore\User', function() {
			throw new NotFoundHttpException('User not found');
		});

	}

	/**
	 * Define the routes for the application.
	 *
	 * @return void
	 */
	public function map()
	{
		$this->loadRoutesFrom(app_path('Http/routes.php'));
	}

}
